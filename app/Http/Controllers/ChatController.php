<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function chat(Request $request)
    {
        $messages = $request->messages;

        $latestMessage = end($messages)['content'];

        // 現在時刻取得
        if (
					str_contains($latestMessage, '時間') ||
					str_contains($latestMessage, '時刻')
        ) {
            return response()->json([
							'message' =>
								'現在時刻は ' .
								Carbon::now('Asia/Tokyo')->format('H:i')
								. ' です。'
            ]);
        }

        // System Prompt
        array_unshift($messages, [
					'role' => 'system',
					'content' => '
						あなたは優秀な日本人AIアシスタントです。
						必ず自然な日本語で回答してください。
					'
        ]);

        // フォールバックモデル
        $models = [
					'openai/gpt-oss-20b:free',
					'mistralai/mistral-small-3.1-24b-instruct:free',
					'meta-llama/llama-3.3-8b-instruct:free',
        ];

        $response = null;
				$usedModel = null;

        // モデル順番試行
        foreach ($models as $model) {

					$response = Http::withHeaders([
						'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),

						'HTTP-Referer' => 'http://localhost',

						'X-Title' => 'My AI Chat'

					])->post(
							'https://openrouter.ai/api/v1/chat/completions',
							[
								'model' => $model,

								// 会話履歴制限
								'messages' => array_slice($messages, -10),

								// 出力制限
								'max_tokens' => 300,
							]
					);

					// 成功判定
					if (
						$response->successful() &&
						isset($response->json()['choices'])
					) {
						$usedModel = $model;

						break;
					}
        }

        // 全モデル失敗
        if (
					!$response ||
					!$response->successful() ||
					!isset($response->json()['choices'])
        ) {

					return response()->json([
						'message' => '現在無料AIが混雑しています。時間を空けて再度お試しください。'
					]);
        }

        // AI返答
        return response()->json([
					'message' => $response->json()['choices'][0]['message']['content'],

					'model' => $usedModel,
					
					'model_name' => match($usedModel) {

						'openai/gpt-oss-20b:free'
							=> 'GPT OSS',

						'mistralai/mistral-small-3.1-24b-instruct:free'
							=> 'Mistral',

						'meta-llama/llama-3.3-8b-instruct:free'
							=> 'Llama',

						default
							=> $usedModel
					}
				]);
    }
}