<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class ApiViaCepController extends Controller
{
    public function show($cep)
    {
        $validator = $this->validateCep($cep);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->all()], 400);
        }
    
        try {
            $apiUrl = Config::get('services.viacep.url');
            $response = Http::withoutVerifying()->get($apiUrl . $cep . '/json');
            $data = $response->json();
            if (isset($data['erro']) && $data['erro'] == true) {
                return response()->json(['message' => 'Esse CEP não existe.'], 404);
            }
            return $data;
        } catch (RequestException $e) {
            return response()->json(['message' => 'Erro ao buscar dados do CEP.'],  $e->getCode() ?: 500);
        }
    }
    
    protected function validateCep($cep)
    {
        return Validator::make(['cep' => $cep], [
            'cep' => ['regex:/^\d{8}$/'],
        ], [
            'cep.regex' => 'O formato do CEP é inválido. Deve conter apenas 8 dígitos numéricos.',
        ]);
    }
    
}