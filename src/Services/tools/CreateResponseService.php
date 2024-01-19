<?php

namespace App\Services\tools;

use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Create the data of response
 */
class CreateResponseService
{
    private array $response;

    /**
     * Recovery of the answer
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * Add the status code of the answer
     *
     * @param int $status_code The Code of the Response Status
     */
    public function statusCode(int $status_code): void
    {
        $this->response['status_code'] = $status_code;
    }

    /**
     * Add validation errors to the answer
     *
     * @param array $errors The table that brings together validation errors
     */
    public function validation(array $errors): void
    {
        $this->response['validation'] = $errors;
    }

    /**
     * Add the support message to the answer
     *
     * @param string $success_message The success message
     */
    public function success(string $success_message): void
    {
        $this->response['success'] = $success_message;
    }

    /**
     * Add Warning's message to the answer
     *
     * @param string $warning_message Warning's message
     */
    public function warning(string $warning_message): void
    {
        $this->response['warning'] = $warning_message;
    }

    /**
     * Add the entity data to the Response
     *
     * @param string $datas entity data
     */
    public function datas(string $datas): void
    {
        $this->response['datas'] = json_decode($datas, true);
    }

    /**
     * Add the critical messages to the response
     *
     * @param string $critical_message The original critical message
     * @param ?string $supplementary_message The additional critical message to add to the original message
     */
    public function criticalError(string $critical_message, ?string $supplementary_message = ''): void
    {
        $critical_message = strlen($supplementary_message) === 0 ?
            $critical_message :
            $critical_message . ' ' . $supplementary_message
        ;

        $this->response['critical_error'] = $critical_message;
    }

    /**
     * Add the exception messages to the response
     *
     * @param Exception $exception L'exception
     */
    public function exception(Exception $exception): void
    {
        $typeException = get_class($exception);

        switch ($typeException) {
            case AuthenticationException::class:
                $message = "Ha..., j'ai un problème..., je ne sais pas qui tu es, je ne peux pas te laisser faire ça.";
                break;
            case BadRequestHttpException::class:
                $message = "Ahhh..., je n'ai pas reussi à récupérer les données que tu viens de saisir.";
                break;
            default:
                $message = "Pardon..., j'ai rencontré un problème, désolé !!!";
                break;
        }

        $this->response['exception'] = $message;
    }

    /**
     * Add a token to the Response
     *
     * @param string $token
     */
    public function token(string $token): void
    {
        $this->response['token'] = $token;
    }
}
