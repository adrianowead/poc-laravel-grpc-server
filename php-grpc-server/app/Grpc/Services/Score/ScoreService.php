<?php

namespace App\Grpc\Services\Score;

use App\Grpc\LaravelValidator;
use App\Models\ScoreModel;
use Illuminate\Support\Str;
use Protobuf\Score;
use Spiral\RoadRunner\GRPC;

class ScoreService implements Score\ScoreServiceInterface
{
    /**
     * Input validator
     * 
     * @var \App\Grpc\Contracts\Validator
     */
    protected $validator;

    /**
     * Create new instance.
     * 
     * @param   \App\Grpc\Contracts\Validator           $validator
     */
    public function __construct(LaravelValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param ContextInterface $ctx
     * @param RegisterRequest $in
     * @return RegisterResponse
     *
     * @throws GRPC\Exception\InvokeException
     */
    public function Register(GRPC\ContextInterface $ctx, Score\RegisterRequest $in): Score\RegisterResponse
    {
        $response = new Score\RegisterResponse;
        $arrayInput = json_decode($in->serializeToJsonString(), true);

        foreach($arrayInput as $input => $val) {
            $arrayInput[Str::snake($input)] = $val;
        }

        $this->validator->validate($arrayInput, [
            'user_id' => 'required|exists:tb_users,id',
            'score' => 'required|numeric|min:0.01',
        ]);

        ScoreModel::setToUserId(
            userId: $arrayInput['user_id'],
            score: $arrayInput['score']
        );

        return $response;
    }

    /**
     * @param GRPC\ContextInterface $ctx
     * @param ByUserIdRequest $in
     * @return ByUserIdResponse
     *
     * @throws GRPC\Exception\InvokeException
     */
    public function ByUserId(GRPC\ContextInterface $ctx, Score\ByUserIdRequest $in): Score\ByUserIdResponse
    {
        $arrayInput = json_decode($in->serializeToJsonString(), true);

        foreach($arrayInput as $input => $val) {
            $arrayInput[Str::snake($input)] = $val;
        }

        $score = ScoreModel::whereUserId($arrayInput['user_id'])->first();

        $response = $this->prepareScoreResponse($score);

        return $response;
    }

    /**
     * Prepare user response.
     * 
     * @param   SocreModel|null        $score
     * 
     * @return  Score\ByUserIdResponse
     * 
     * @throws GRPC\Exception\InvokeException   
     */
    protected function prepareScoreResponse(ScoreModel $score = null): Score\ByUserIdResponse
    {
        if(!$score) {
            throw new GRPC\Exception\InvokeException(
                "Score not found.",
                GRPC\StatusCode::NOT_FOUND
            );
        }

        $response = new Score\ByUserIdResponse;

        $response->setScore($score->score);

        return $response;
    }
}