<?php
namespace App\DataFixtures;

use App\Entity\MailTemplate;
use App\Entity\User;
use App\Repository\MailTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;
    private $mailTemplateRepository; 
    public function __construct(UserPasswordHasherInterface $hasher, MailTemplateRepository $mailTemplateRepository)
    {
        $this->hasher = $hasher;
        $this->mailTemplateRepository = $mailTemplateRepository; 
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        // $user = new User();
        // $user->setEmail('admin@example.com');
        // $password = $this->hasher->hashPassword($user, 'admin');
        // $user->setPassword($password);
        // $user->setRoles(["ROLE_ADMIN"]);
        
        $mailTemplate = new MailTemplate(); 
        $mailTemplate->setSujet("Sujet du mail"); 
        $mailTemplate->setContenu("<p>Bonjour,&nbsp;</p><p>Nous avons le plaisir de vous annoncer votre recrutement au sein de l&#39;entreprise &lt;&lt;NOM_ENTREPRISE&gt;&gt; en tant que &lt;&lt;POSTE&gt;&gt; pour un &lt;&lt;TYPE_CONTRAT&gt;&gt;</p><p>Afin que nous puissioon finaliser votre embauche nous avons besoin de certaines informations vous concernant ainsi que quelques document.&nbsp;</p><p>Nous vous invitons donc &agrave; remplir le formulaire en cliquant sur le lien suivant:</p><p>&lt;&lt;LIEN_VERS_LE_FORMULAIRE&gt;&gt;.&nbsp;</p><p>Avant de vous lancer dans le formulaire veuillez r&eacute;unir les documents suivants:&nbsp;</p><p>&lt;&lt;LISTE_DOCUMENTS&gt;&gt;.&nbsp;</p><p>Cordialement,&nbsp;</p><p><img alt=\"\" src=\"http://localhost:8081/images_mail_template/Capture86.PNG\" style=\"height:104px; width:200px\" /></p><p>&nbsp;</p>");
        // $mailTemplate = $this->mailTemplateRepository->findAll()[0]; 
        $mailTemplate->setPj(["Ajoutez des pjs ici"]); 
        
        $manager->persist($mailTemplate); 
        // $manager->persist($user);
        $manager->flush();
    }
}