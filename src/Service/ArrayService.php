<?php


namespace App\Service;


use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class ArrayService
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * <b>Array Diff</b>
     *
     * Cette fonction véfirie si la structure du tableau passée en paramètre correspond à la structure de requise
     *
     * Elle renvoie un vide en cas de succès ou une exception au cas où il y aurait une différence
     *
     * @param array $structure
     * @param array $data
     */
    public function array_diff(array $structure, array $data): void
    {
        if(array_diff($structure, array_keys($data)) || array_diff(array_keys($data), $structure))
        {
            if(array_diff($structure, array_keys($data)))
            {
                $this->logger->warning('>>>>> Désolé, il y a des paramètres manquants dans la structure du client', [
                    'Not in Client structure' => array_diff($structure, array_keys($data))
                ]);
            }

            if (array_diff(array_keys($data), $structure))
            {
                $this->logger->warning('>>>>> Désolé, il y a des paramètres de trop dans la structure du client', [
                    'Not in Platform structure' => array_diff(array_keys($data), $structure)
                ]);
            }

            throw new \RuntimeException("Désolé, la structure de vos paramètres est incoherente. Veuillez consulter la documentation.", Response::HTTP_NOT_ACCEPTABLE);
        }
    }

    public function notInArray(array $structure, array $data)
    {
        foreach ($structure as $value)
        {
            if (!array_key_exists($value, $data)) {
                throw new \RuntimeException("Désolé, la structure de vos paramètres est incoherente.", Response::HTTP_BAD_REQUEST);
            }
        }
    }
}