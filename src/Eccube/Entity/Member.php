<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Member
 *
 * @ORM\Table(name="dtb_member")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="discriminator_type", type="string", length=255)
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity(repositoryClass="Eccube\Repository\MemberRepository")
 */
class Member extends \Eccube\Entity\AbstractEntity implements UserInterface
{
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity(array(
            'fields'  => 'login_id',
            'message' => '既に利用されているログインIDです'
        )));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return array('ROLE_ADMIN');
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->login_id;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }

    /**
     * @var int
     *
     * @ORM\Column(name="member_id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="department", type="string", length=255, nullable=true)
     */
    private $department;

    /**
     * @var string
     *
     * @ORM\Column(name="login_id", type="string", length=255)
     */
    private $login_id;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=255)
     */
    private $salt;

    /**
     * @var int
     *
     * @ORM\Column(name="rank", type="smallint", options={"unsigned":true})
     */
    private $rank;

    /**
     * @var int
     *
     * @ORM\Column(name="del_flg", type="smallint", options={"unsigned":true,"default":0})
     */
    private $del_flg = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_date", type="datetime")
     */
    private $create_date;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="update_date", type="datetime")
     */
    private $update_date;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="login_date", type="datetime", nullable=true)
     */
    private $login_date;

    /**
     * @var \Eccube\Entity\Master\Work
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Work")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="work", referencedColumnName="id")
     * })
     */
    private $Work;

    /**
     * @var \Eccube\Entity\Master\Authority
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Master\Authority")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="authority", referencedColumnName="id")
     * })
     */
    private $Authority;

    /**
     * @var \Eccube\Entity\Member
     *
     * @ORM\ManyToOne(targetEntity="Eccube\Entity\Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="creator_id", referencedColumnName="member_id")
     * })
     */
    private $Creator;


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
     * Set name.
     *
     * @param string|null $name
     *
     * @return Member
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set department.
     *
     * @param string|null $department
     *
     * @return Member
     */
    public function setDepartment($department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department.
     *
     * @return string|null
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set loginId.
     *
     * @param string $loginId
     *
     * @return Member
     */
    public function setLoginId($loginId)
    {
        $this->login_id = $loginId;

        return $this;
    }

    /**
     * Get loginId.
     *
     * @return string
     */
    public function getLoginId()
    {
        return $this->login_id;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return Member
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt.
     *
     * @param string $salt
     *
     * @return Member
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt.
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set rank.
     *
     * @param int $rank
     *
     * @return Member
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank.
     *
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Set delFlg.
     *
     * @param int $delFlg
     *
     * @return Member
     */
    public function setDelFlg($delFlg)
    {
        $this->del_flg = $delFlg;

        return $this;
    }

    /**
     * Get delFlg.
     *
     * @return int
     */
    public function getDelFlg()
    {
        return $this->del_flg;
    }

    /**
     * Set createDate.
     *
     * @param \DateTime $createDate
     *
     * @return Member
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get createDate.
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set updateDate.
     *
     * @param \DateTime $updateDate
     *
     * @return Member
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * Get updateDate.
     *
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * Set loginDate.
     *
     * @param \DateTime|null $loginDate
     *
     * @return Member
     */
    public function setLoginDate($loginDate = null)
    {
        $this->login_date = $loginDate;

        return $this;
    }

    /**
     * Get loginDate.
     *
     * @return \DateTime|null
     */
    public function getLoginDate()
    {
        return $this->login_date;
    }

    /**
     * Set work.
     *
     * @param \Eccube\Entity\Master\Work|null $work
     *
     * @return Member
     */
    public function setWork(\Eccube\Entity\Master\Work $work = null)
    {
        $this->Work = $work;

        return $this;
    }

    /**
     * Get work.
     *
     * @return \Eccube\Entity\Master\Work|null
     */
    public function getWork()
    {
        return $this->Work;
    }

    /**
     * Set authority.
     *
     * @param \Eccube\Entity\Master\Authority|null $authority
     *
     * @return Member
     */
    public function setAuthority(\Eccube\Entity\Master\Authority $authority = null)
    {
        $this->Authority = $authority;

        return $this;
    }

    /**
     * Get authority.
     *
     * @return \Eccube\Entity\Master\Authority|null
     */
    public function getAuthority()
    {
        return $this->Authority;
    }

    /**
     * Set creator.
     *
     * @param \Eccube\Entity\Member|null $creator
     *
     * @return Member
     */
    public function setCreator(\Eccube\Entity\Member $creator = null)
    {
        $this->Creator = $creator;

        return $this;
    }

    /**
     * Get creator.
     *
     * @return \Eccube\Entity\Member|null
     */
    public function getCreator()
    {
        return $this->Creator;
    }
}
