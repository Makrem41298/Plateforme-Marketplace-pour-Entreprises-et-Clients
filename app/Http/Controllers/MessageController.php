<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use App\Models\Entreprise;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    use apiResponse;


    public function send(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|integer',
                'receiver_type' => ['required', Rule::in(['user', 'entreprise', 'admin'])],
                'content' => 'required|string|max:2000',
            ]);

            if ($validator->fails()) {
                return $this->apiResponse('Validation error', $validator->errors(), 422);
            }

            $userData = $this->checkUser();





            $receiverClass = $this->getReceiverClass($request->receiver_type);

            $message = Message::create([
                'sender_id' => $userData['sender']->id,
                'sender_type' => get_class($userData['sender']),
                'receiver_id' => $validator->validated()['receiver_id'],
                'receiver_type' => $receiverClass,
                'content' => $validator->validated()['content']
            ]);
            broadcast(new MessageSent($message))->toOthers();


            return $this->apiResponse('Message sent successfully', $message, 201);

        } catch (\Exception $e) {
            return $this->apiResponse('Message sending failed: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/messages",
     *     summary="Get all messages",
     *     @OA\Response(
     *         response=200,
     *         description="Messages retrieved successfully",
     *         @OA\JsonContent(
     *             example={"status": "success", "message": "Messages retrieved successfully", "data": {"current_page": 1, "data": {{"id": 1, "content": "Hello!", "sender": {}, "receiver": {}}}}
     *         )
     *     )
     * )
     */
    public function index()
    {
        try {
            $userData = $this->checkUser();

            $messages = Message::where(function($query) use ($userData) {
                $query->where('sender_id', $userData['sender']->id)
                    ->where('sender_type', get_class($userData['sender']));
            })
                ->orWhere(function($query) use ($userData) {
                    $query->where('receiver_id', $userData['sender']->id)
                        ->where('receiver_type', get_class($userData['sender']));
                })
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return $this->apiResponse('Messages retrieved successfully', $messages);

        } catch (\Exception $e) {
            return $this->apiResponse('Failed to retrieve messages: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/conversation/{receiverId}/{receiverType}",
     *     summary="Get conversation",
     *     @OA\Response(
     *         response=200,
     *         description="Conversation retrieved successfully",
     *         @OA\JsonContent(
     *             example={"status": "success", "message": "Conversation retrieved successfully", "data": {"current_page": 1, "data": {{"id": 1, "content": "Hello!", "read_at": null}}}}
     *         )
     *     )
     * )
     */
    public function conversation($receiverId, $receiverType)
    {
        try {
            $userData = $this->checkUser();
            $sender = $userData['sender'];
            $senderClass = get_class($sender);

            $receiverClass = $this->getReceiverClass($receiverType);

            $messages = Message::where(function ($query) use ($sender, $senderClass, $receiverId, $receiverClass) {
                $query->where('sender_id', $sender->id)
                    ->where('sender_type', $senderClass)
                    ->where('receiver_id', $receiverId)
                    ->where('receiver_type', $receiverClass);
            })
                ->orWhere(function ($query) use ($sender, $senderClass, $receiverId, $receiverClass) {
                    $query->where('sender_id', $receiverId)
                        ->where('sender_type', $receiverClass)
                        ->where('receiver_id', $sender->id)
                        ->where('receiver_type', $senderClass);
                })
                ->with(['sender', 'receiver']) // eager load relationships
                ->orderBy('created_at', 'asc')
                ->get();

            return $this->apiResponse('Conversation retrieved successfully', $messages, 200);

        } catch (\Throwable $e) {
            return $this->apiResponse('Failed to retrieve conversation: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/messages/{id}/read",
     *     summary="Mark message as read",
     *     @OA\Response(
     *         response=200,
     *         description="Message marked as read",
     *         @OA\JsonContent(
     *             example={"status": "success", "message": "Message marked as read", "data": {"id": 1, "read_at": "2023-01-01 12:00:00"}}
     *         )
     *     )
     * )
     */
    public function markAsRead($id)
    {
        try {
            $userData = $this->checkUser();


            $message = Message::where('receiver_id', $userData['sender']->id)
                ->where('receiver_type', get_class($userData['sender']))
                ->findOrFail($id);

            if (!$message->read_at) {
                $message->update(['read_at' => now()]);
            }

            return $this->apiResponse('Message marked as read', $message);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Message not found', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse('Failed to mark message as read: ' . $e->getMessage(), null, 500);
        }
    }

    private function checkUser()
    {
        $sender = null;
        $guard = null;

        if (auth('admin')->check()) {
            $sender = auth('admin')->user();
            $guard = 'admin';
        } elseif (auth('entreprise')->check()) {
            $sender = auth('entreprise')->user();
            $guard = 'entreprise';
        } elseif (auth()->check()) {
            $sender = auth()->user();
            $guard = 'user';
        }

        return [
            'sender' => $sender,
            'guard' => $guard
        ];
    }

    private function getReceiverClass($receiverType)
    {
        return match($receiverType) {
            'user' => User::class,
            'entreprise' => Entreprise::class,
            default => throw new \InvalidArgumentException('Invalid receiver type')
        };
    }
}
