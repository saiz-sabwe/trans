<?php


namespace App\Service;


use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JetBrains\PhpStorm\ArrayShape;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;


class ExceptionService
{
    private LoggerInterface $logger;
    private string $messageException = "Désolé, une erreur inconnue est survenue dans le système. Veuillez réessayer plus tard.";
    private int $codeException = Response::HTTP_BAD_REQUEST;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getException(\Exception $e = null, string $defaultMessage = null): array
    {

        return $e === null ? $this->nullException($defaultMessage) : $this->exception($e, $defaultMessage);
    }

    #[ArrayShape(['message' => "string", "code" => "int"])]
    private function exception(\Exception $e, string $defaultMessage = null): array
    {
        //Si le message par défaut est null, vérifier un certains nombre de message par défaut
        if(null === $defaultMessage)
        {
            $defaultMessage = $this->getDefaultMessage($e);
        }

        $message = $defaultMessage ?? $e->getMessage();

        $this->isValidHttpCode((int)$e->getCode());
        $this->messageException = $message ?: $this->messageException;

        $this->logger->error(">>>>> Code exception 0: " . $this->codeException, []);
        $this->logger->error(">>>>> Message exception 1: " . $this->messageException, [$e]);

        return ['message' => $this->messageException, "code" => $this->codeException];
    }

    #[ArrayShape(['message' => "string", "code" => "int"])]
    private function nullException(string $defaultMessage = null): array
    {
        $this->messageException = $defaultMessage ?? $this->messageException;

        $this->logger->error(">>>>> 2: " . $this->messageException, []);

        return ['message' => $this->messageException, "code" => $this->codeException];
    }

    private function getDefaultMessage(\Exception $exception): ?string
    {
        $msg = null;

        if($exception instanceof TooManyLoginAttemptsAuthenticationException)
        {
            $msg = strtr("STOP, le compte est bloqué car vous avez atteint la limite de tentatives. "
                . "Veuillez réessayer dans %minutes% minutes", $exception->getMessageData());
        }
        elseif ($exception instanceof BadCredentialsException || $exception instanceof UserNotFoundException)
        {
            $msg = "Désolé, le compte utilisateur et/ou le mot de passe fournis sont erronés.";
        }
//        elseif (($exception instanceof CannotDecodeConte) || ($exception instanceof CannotEncodeContent))
//        {
//            $msg = "Désolé, impossible de crypter/décrypter le jeton fourni car, il est invalide";
//            //"Error while decoding from Base64Url, invalid base64 characters detected"
//        }
        elseif ($exception instanceof JsonException)
        {
            $msg = "Désolé, la structure du JSON fourni est invalide.";
        }
        elseif ($exception instanceof Exception\UniqueConstraintViolationException)
        {
            $msg = "Désolé, une information identique existe déjà dans la base de données.";
        }
        elseif ($exception instanceof NonUniqueResultException)
        {
            $msg = "Désolé, il se pourrait que l'information demandée ait des doublons.";
        }
        elseif ($exception instanceof NoResultException)
        {
            $msg = "Désolé, aucune information n'a été trouvé.";
        }
        elseif ($exception instanceof Exception\NotNullConstraintViolationException)
        {
            $msg = "Désolé, vos données contiennent une valeur Null non autorisée.";
        }
        elseif ($exception instanceof ConversionException)
        {
            $msg = "Désolé, une erreur est survenue lors de la conversion de la valeur. Il se pourrait que la valeur passée ne soit pas du type Uuid";
        }
        elseif ($exception instanceof Exception)
        {
            $msg = "Désolé, une erreur est survenue lors du traitement de données.";
        }
        elseif ($exception instanceof \ErrorException)
        {
            $msg = "Désolé, une erreur est survenue lors du traitement de données.";
        }
//        elseif ($exception instanceof RestException)
//        {
//            $msg = "Désolé, une erreur est survenue lors du traitement de l'OTP.";
//
//            if(str_contains($exception->getMessage(), 'VerificationCheck'))
//            {
//                $msg = $msg . " Vérifiez que le numéro et/ou l'OTP sont corrects";
//            }
//        }
        else
        {
            $this->logger->error(">>>>> EXCEPTION TYPE: " . get_class($exception));
        }

        return $msg;
    }

    private function isValidHttpCode(int $httpCode)
    {
        if($httpCode >= 100 && $httpCode <= 599)
        {
            $this->codeException = $httpCode;
        }
        else
        {
            $this->codeException = Response::HTTP_BAD_REQUEST;
        }
    }
}