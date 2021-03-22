<?php

/*
 * This file is part of the Incipio package.
 *
 * (c) Florian Lefevre
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Project;

use Doctrine\ORM\Mapping as ORM;

/**
 * RepartitionJEH.
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class RepartitionJEH
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Mission
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Project\Mission", inversedBy="repartitionsJEH")
     */
    private $mission;

    /**
     * @var int
     *
     * @ORM\Column(name="nombreJEH", type="integer", nullable=true)
     */
    private $nbrJEH;

    /**
     * @var int
     *
     * @ORM\Column(name="prixJEH", type="integer", nullable=true)
     */
    private $prixJEH;

    /**
     * @var AvMission
     *
     * @ORM\ManyToOne(targetEntity="AvMission", inversedBy="nouvelleRepartition")
     */
    private $avMission;

    /**
     * @var Phase
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Project\Phase", inversedBy="repartitionsJEH", cascade={"remove"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $phase;

    public function __toString()
    {
        return '(id :' . $this->id . ') ' . $this->nbrJEH . ' * ' . $this->prixJEH;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set mission.
     *
     * @param Mission $mission
     *
     * @return RepartitionJEH
     */
    public function setMission(Mission $mission = null)
    {
        $this->mission = $mission;

        return $this;
    }

    /**
     * Get mission.
     *
     * @return Mission
     */
    public function getMission()
    {
        return $this->mission;
    }

    /**
     * Set nbrJEH.
     *
     * @param int $nbrJEH
     *
     * @return RepartitionJEH
     */
    public function setNbrJEH($nbrJEH)
    {
        $this->nbrJEH = $nbrJEH;

        return $this;
    }

    /**
     * Get nbrJEH.
     *
     * @return int
     */
    public function getNbrJEH()
    {
        return $this->nbrJEH;
    }

    /**
     * Set prixJEH.
     *
     * @param int $prixJEH
     *
     * @return RepartitionJEH
     */
    public function setPrixJEH($prixJEH)
    {
        $this->prixJEH = $prixJEH;

        return $this;
    }

    /**
     * Get prixJEH.
     *
     * @return int
     */
    public function getPrixJEH()
    {
        return $this->prixJEH;
    }

    /**
     * Set avMission.
     *
     * @param AvMission $avMission
     *
     * @return RepartitionJEH
     */
    public function setAvMission(AvMission $avMission = null)
    {
        $this->avMission = $avMission;

        return $this;
    }

    /**
     * Get avMission.
     *
     * @return AvMission
     */
    public function getAvMission()
    {
        return $this->avMission;
    }

    /**
     * @return mixed
     */
    public function getPhase()
    {
        return $this->phase;
    }

    /**
     * @param mixed $phase
     */
    public function setPhase($phase)
    {
        $this->phase = $phase;
    }
}
