<?php

namespace App\Http\Controllers;

use App\Models\Message;
use GuzzleHttp\Psr7\MessageTrait;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MessageController extends Controller
{
    use apiResponse;
    public function conversation($receiverId,$receiverType)
    {
        try {
            $user=$this->checkUser($receiverType);
            $sender=$user->sender;
            $receiverClass=$user->receiver;


            $messages = Message::where(function($query) use ($sender, $receiverId, $receiverClass) {
                $query->where('sender_id', $sender->id)
                    ->where('sender_type', get_class($sender))
                    ->where('receiver_id', $receiverId)
                    ->where('receiver_type', $receiverClass);
            })
                ->orWhere(function($query) use ($sender, $receiverId, $receiverClass) {
                    $query->where('sender_id', $receiverId)
                        ->where('sender_type', $receiverClass)
                        ->where('receiver_id', $sender->id)
                        ->where('receiver_type', get_class($sender));
                })
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'asc')
                ->paginate(20);

            return $this->apiResponse('Conversation retrieved successfully', $messages);

        }catch (ModelNotFoundException $e){
            return $this->apiResponse('Conversation not found', [], 404);
        } catch (\Exception $e) {
            return $this->apiResponse('Failed to retrieve conversation', null, 500);
        }
    }
    public function send(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required|integer',
                'receiver_type' => ['required', Rule::in(['user', 'entreprise','admin'])],
                'content' => 'required|string|max:2000',
            ]);

            if ($validator->fails()) {
                return $this->apiResponse('Validation error', $validator->errors(), 422);
            }
            $validatorData=$validator->validated();

             $user=$this->checkUser($validatorData['receiver_type']);
            $sender = $user->sender;
            $receiverType =$sender->receiver;
            $message = Message::create([
                'sender_id' => $sender->id,
                'sender_type' => get_class($sender),
                'receiver_id' => $validatorData['receiver_id'],
                'receiver_type' => $receiverType,
                'content' => $validatorData['content']
            ]);

            return $this->apiResponse('Message sent successfully', $message, 201);

        } catch (\Exception $e) {
            return $this->apiResponse('Message sending failed: ' . $e->getMessage(), null, 500);
        }
    }

    public function markAsRead($id)
    {
        try {
            $user = auth('client')->check() ? auth()->user() :
                (auth('admin')->check() ? auth('admin')->user() :
                    (auth('entreprise')->check() ? auth('entreprise')->user() : null));




            $message = Message::where('receiver_id', $user->id)
                ->where('receiver_type', get_class($user))
                ->findOrFail($id);

            if (!$message->read_at) {
                $message->update(['read_at' => now()]);
            }

            return $this->apiResponse('Message marked as read', $message);

        } catch (ModelNotFoundException $e) {
            return $this->apiResponse('Message not found', null, 404);
        } catch (\Exception $e) {
            return $this->apiResponse('Failed to mark message as read', null, 500);
        }
    }
    private function checkUser($receiverType)
    {
        $sender = auth('client')->check() ? auth()->user() :
            (auth('admin')->check() ? auth('admin')->user() :
                (auth('entreprise')->check() ? auth('entreprise')->user() : null));


        $receiverClass = $receiverType === 'user' ? 'App\Models\User'
            :( $receiverType === 'entreprise'?'App\Models\Entreprise':'App\Models\Admin') ;
        return ['sender' => $sender, 'receiver' => $receiverClass];


    }

}
