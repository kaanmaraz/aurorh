<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    public const NB_CHAR_MDP = 12;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\OneToMany(mappedBy: 'utilisateur', targetEntity: Lien::class, orphanRemoval: true)]
    private Collection $liens;

    public function __construct()
    {
        $this->liens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Lien>
     */
    public function getLiens(): Collection
    {
        return $this->liens;
    }

    public function addLien(Lien $lien): self
    {
        if (!$this->liens->contains($lien)) {
            $this->liens->add($lien);
            $lien->setUtilisateur($this);
        }

        return $this;
    }

    public function removeLien(Lien $lien): self
    {
        if ($this->liens->removeElement($lien)) {
            // set the owning side to null (unless already changed)
            if ($lien->getUtilisateur() === $this) {
                $lien->setUtilisateur(null);
            }
        }

        return $this;
    }

    
    public function getMdpGenere(): ?string
    {
        //////Cr??ation d'un mot de passe g??n??r?? avec des caract??res al??atoires//////
        // Liste des caract??res possibles
        $cars = 'azertyiopqsdfghjklmwxcvbn0123456789';
        //la chaine de caract??re qui sera remplie
        $mdp = '';
        $long = strlen($cars);

        srand((float) microtime() * 1000000);
        //Initialise le g??n??rateur de nombres al??atoires
        //Cr??er la chaine de caract??re avec 12 caract??res
        for ($i = 0; $i < $this::NB_CHAR_MDP; ++$i) {
            $mdp = $mdp.substr($cars, rand(0, $long - 1), 1);
        }
        return $mdp;
    }
}
