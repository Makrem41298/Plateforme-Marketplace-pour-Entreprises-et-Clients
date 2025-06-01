<?php

namespace App\Http\Controllers;

use App\Models\Contrat;
use App\Models\Entreprise;
use Illuminate\Http\Request;

class PaymentController extends Controller

{
use apiResponse;

    public function initiateCheckout($reference)
    {
        $query= Contrat::where('reference', $reference);
        $query->withCount('transactions');
      $contract= $query->first();


        $lineItems = [
            [
                'price_data' => [
                    'currency'     => env('CASHIER_CURRENCY', 'usd'),
                    'product_data' => [
                        'name' => 'Première tranche du contrat: ' . $contract->reference,
                    ],
                    'unit_amount'  =>$contract->transactions_count===0?(int) round($contract->montant_total * 0.3 * 100):(int) round($contract->montant_total*0.7* 100),
                ],
                'quantity' => 1,
            ]
        ];

        $sessionOptions = [
            'line_items'   => $lineItems,
            'mode'         => 'payment',
            'success_url' => 'http://localhost:5173/payment/success?reference=' . $reference . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost:5173/payment/cancel?reference=' . $reference,
            'metadata'     => [
                'contract_reference' => $reference,
                'user_id' => auth()->id(),
                'payment_type' => 'premiere_tranche'
            ],
        ];

        $session=auth()->user()->checkout(null, $sessionOptions);
        return $this->apiResponse('url', $session->url,200);

    }
    public function success($reference)
    {



        $query= Contrat::where('reference', $reference);
        $query->withCount('transactions');

        $contrat= $query->first();

        $entreprise= $contrat->offre->entreprise;




        if (!$contrat) {
            return $this->apiResponse('Contrat non trouvé', null, 404);
        }

        // Création d'une transaction associée
        $contrat->transactions()->create([
            'date_effctue' => now(),
            'statut' => 'effctue',
            'tranch' => $contrat->transactions_count===0?1:2,
            'methode_paiment' => 'carte_credit', // ou autre selon contexte
        ]);
        if ($contrat->transactions_count===0){
            $contrat->offre->projet->update([
                'status' => 'en_cours',
            ]);

            $contrat->update([
                'statut' => 'signe',
                'signe_le' => now(),
            ]);
        }else{
            $entreprise->profile()->increment('solde', $contrat->montant_total);


        }




        return $this->apiResponse('success', $reference, 200);
    }


}
