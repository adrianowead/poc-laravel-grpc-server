<?php

namespace App\Grpc\Services\Identity;

use App\Grpc\LaravelValidator;
use Protobuf\Identity;
use App\Jobs\RegisterUser;
use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;
use Spiral\RoadRunner\GRPC\ContextInterface;
use Spiral\RoadRunner\GRPC\Exception\InvokeException;
use Spiral\RoadRunner\GRPC\StatusCode;

class AuthService implements Identity\AuthServiceInterface
{
    /**
     * Input validator
     * 
     * @var \App\Grpc\Contracts\Validator
     */
    protected $validator;

    /**
     * Hasher
     * 
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * Create new instance.
     * 
     * @param   \App\Grpc\Contracts\Validator           $validator
     * @param   \Illuminate\Contracts\Hashing\Hasher    $hasher
     */
    public function __construct(LaravelValidator $validator, Hasher $hasher)
    {
        $this->validator = $validator;
        $this->hasher = $hasher;
    }

    /**
     * @param ContextInterface $ctx
     * @param RegisterRequest $in
     * @return RegisterResponse
     *
     * @throws GRPC\Exception\InvokeException
     */
    public function Register(ContextInterface $ctx, Identity\RegisterRequest $in): Identity\RegisterResponse
    {
        $response = new Identity\RegisterResponse;
        $arrayInput = json_decode($in->serializeToJsonString(), true);

        $this->validator->validate($arrayInput, [
            'email' => 'bail|required|email|unique:tb_users,email',
            'name' => 'required|max:255',
            'password' => 'required|confirmed',
        ]);

        RegisterUser::dispatch($arrayInput);

        return $response;
    }

    /**
     * @param ContextInterface $ctx
     * @param LoginRequest $in
     * @return UserResponse
     *
     * @throws GRPC\Exception\InvokeException
     */
    public function Login(ContextInterface $ctx, Identity\LoginRequest $in): Identity\UserResponse
    {
        $arrayInput = json_decode($in->serializeToJsonString(), true);

        $this->validator->validate($arrayInput, [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $arrayInput['email'])->first();

        if(!$this->hasher->check($arrayInput['password'], $user->password)) {
            throw new InvokeException(
                "These credentials do not match our records.",
                StatusCode::INVALID_ARGUMENT
            );
        }

        $response = $this->prepareUserResponse($user);

        return $response;
    }

    /**
     * @param ContextInterface $ctx
     * @param UserByEmailRequest $in
     * @return UserResponse
     *
     * @throws GRPC\Exception\InvokeException
     */
    public function UserByEmail(ContextInterface $ctx, Identity\UserByEmailRequest $in): Identity\UserResponse
    {
        $arrayInput = json_decode($in->serializeToJsonString(), true);

        $this->validator->validate($arrayInput, [
            'email' => 'required|email'
        ]);

        $user = User::where('email', $arrayInput['email'])->first();
        $response = $this->prepareUserResponse($user);

        return $response;
    }

    /**
     * @param ContextInterface $ctx
     * @param UserByIdRequest $in
     * @return UserResponse
     *
     * @throws GRPC\Exception\InvokeException
     */
    public function UserById(ContextInterface $ctx, Identity\UserByIdRequest $in): Identity\UserResponse
    {
        $user = User::find($in->getId());
        $response = $this->prepareUserResponse($user);

        return $response;
    }

    /**
     * Prepare user response.
     * 
     * @param   User|null               $user
     * 
     * @return  Identity\UserResponse
     * 
     * @throws GRPC\Exception\InvokeException   
     */
    protected function prepareUserResponse(User $user = null): Identity\UserResponse
    {
        if(!$user) {
            throw new InvokeException("These credentials do not match our records.", StatusCode::INVALID_ARGUMENT);
        }

        $response = new Identity\UserResponse;

        $response->setId($user->id);
        $response->setEmail($user->email);
        $response->setName($user->name);

        return $response;
    }
}