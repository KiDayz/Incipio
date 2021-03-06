<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Comment;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\ChangeTrackingPolicy("DEFERRED_EXPLICIT")
 */
class Thread extends AbstractThread
{
    /**
     * @var int
     *
     * ORM\Column(name="id", type="integer")
     * @ORM\Column(type="string")
     * @ORM\Id
     * ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
}
