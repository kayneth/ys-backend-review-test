<?php

declare(strict_types=1);

namespace App\Validation;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;

class CreateEventConstraint implements ConstraintInterface
{
    public function get(): Constraint
    {
        return new Constraints\Collection([
            'allowExtraFields' => true,
            'fields' => [
                'id' => new Constraints\NotBlank(),
                'type' => new Constraints\NotBlank(),
                'created_at' => new Constraints\DateTime([
                    'format' => 'Y-m-d\TH:i:s\Z',
                ]),
                'public' => new Constraints\Type(['type' => 'bool']),
                'payload' => new Constraints\Type(['type' => 'array']),
                'repo' => new Constraints\Collection([
                    'fields' => [
                        'id' => new Constraints\Type(['type' => 'integer']),
                        'name' => new Constraints\NotBlank(),
                        'url' => [
                            new Constraints\NotBlank(),
                            new Constraints\Type(['type' => 'string']),
                        ],
                    ],
                    'allowExtraFields' => true,
                ]),
                'actor' => new Constraints\Collection([
                    'fields' => [
                        'id' => new Constraints\Type(['type' => 'integer']),
                        'login' => new Constraints\NotBlank(),
                        'avatar_url' => [
                            new Constraints\NotBlank(),
                            new Constraints\Type(['type' => 'string']),
                        ],
                        'url' => [
                            new Constraints\NotBlank(),
                            new Constraints\Type(['type' => 'string']),
                        ],
                    ],
                    'allowExtraFields' => true,
                ]),
                'org' => new Constraints\Optional([
                    new Constraints\Collection([
                        'fields' => [
                            'id' => new Constraints\Type(['type' => 'integer']),
                            'login' => new Constraints\NotBlank(),
                            'avatar_url' => [
                                new Constraints\NotBlank(),
                                new Constraints\Type(['type' => 'string']),
                            ],
                            'url' => [
                                new Constraints\NotBlank(),
                                new Constraints\Type(['type' => 'string']),
                            ],
                        ],
                        'allowExtraFields' => true,
                    ])
                ]),
            ]
        ]);
    }
}
