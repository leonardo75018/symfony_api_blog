<?php

namespace App\Service;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{

    private $v;

    public function __construct(ValidatorInterface $validatorInterface)
    {
        $this->v = $validatorInterface;
    }


    public function isValid($obj)
    {
        $errors = $this->v->validate($obj);

        if (count($errors) > 0) {
            foreach ($errors as $e) {
                $e_list[] = $e->getMessage();
            }
            return $e_list;
        }

        return true;
    }
}
