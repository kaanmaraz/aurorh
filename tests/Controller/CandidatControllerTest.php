<?php

namespace App\Test\Controller;

use App\Entity\Candidat;
use App\Repository\CandidatRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CandidatControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private CandidatRepository $repository;
    private string $path = '/candidat/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(Candidat::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Candidat index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $originalNumObjectsInRepository = count($this->repository->findAll());

        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'candidat[nom]' => 'Testing',
            'candidat[prenom]' => 'Testing',
            'candidat[adresse]' => 'Testing',
            'candidat[codePostal]' => 'Testing',
            'candidat[ville]' => 'Testing',
            'candidat[dateDeNaissance]' => 'Testing',
            'candidat[villeNaissance]' => 'Testing',
            'candidat[departementNaissance]' => 'Testing',
            'candidat[paysNaissance]' => 'Testing',
            'candidat[numeroSs]' => 'Testing',
            'candidat[nomUsage]' => 'Testing',
            'candidat[completementAdresse]' => 'Testing',
            'candidat[dateExpirationTs]' => 'Testing',
            'candidat[sexe]' => 'Testing',
            'candidat[email]' => 'Testing',
            'candidat[datePrevisEmbauche]' => 'Testing',
            'candidat[poste]' => 'Testing',
            'candidat[site]' => 'Testing',
            'candidat[delaiFormulaire]' => 'Testing',
            'candidat[mdp]' => 'Testing',
            'candidat[numeroAgent]' => 'Testing',
            'candidat[debutCDD]' => 'Testing',
            'candidat[finCDD]' => 'Testing',
            'candidat[service]' => 'Testing',
            'candidat[coeffDeveloppe]' => 'Testing',
            'candidat[ptsGarantie]' => 'Testing',
            'candidat[niveauSalaire]' => 'Testing',
            'candidat[coeffBase]' => 'Testing',
            'candidat[ptsCompetences]' => 'Testing',
            'candidat[periodeEssai]' => 'Testing',
            'candidat[ptsExperience]' => 'Testing',
            'candidat[prime]' => 'Testing',
            'candidat[aDiplome]' => 'Testing',
            'candidat[nationnalite]' => 'Testing',
            'candidat[typeNature]' => 'Testing',
            'candidat[typeReferentiel]' => 'Testing',
            'candidat[dejaComplete]' => 'Testing',
            'candidat[supprime]' => 'Testing',
            'candidat[dateSuppression]' => 'Testing',
            'candidat[numeroAgentManager]' => 'Testing',
            'candidat[cle]' => 'Testing',
            'candidat[lien]' => 'Testing',
            'candidat[typeCandidat]' => 'Testing',
        ]);

        self::assertResponseRedirects('/candidat/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Candidat();
        $fixture->setNom('My Title');
        $fixture->setPrenom('My Title');
        $fixture->setAdresse('My Title');
        $fixture->setCodePostal('My Title');
        $fixture->setVille('My Title');
        $fixture->setDateDeNaissance('My Title');
        $fixture->setVilleNaissance('My Title');
        $fixture->setDepartementNaissance('My Title');
        $fixture->setPaysNaissance('My Title');
        $fixture->setNumeroSs('My Title');
        $fixture->setNomUsage('My Title');
        $fixture->setCompletementAdresse('My Title');
        $fixture->setDateExpirationTs('My Title');
        $fixture->setSexe('My Title');
        $fixture->setEmail('My Title');
        $fixture->setDatePrevisEmbauche('My Title');
        $fixture->setPoste('My Title');
        $fixture->setSite('My Title');
        $fixture->setDelaiFormulaire('My Title');
        $fixture->setMdp('My Title');
        $fixture->setNumeroAgent('My Title');
        $fixture->setDebutCDD('My Title');
        $fixture->setFinCDD('My Title');
        $fixture->setService('My Title');
        $fixture->setCoeffDeveloppe('My Title');
        $fixture->setPtsGarantie('My Title');
        $fixture->setNiveauSalaire('My Title');
        $fixture->setCoeffBase('My Title');
        $fixture->setPtsCompetences('My Title');
        $fixture->setPeriodeEssai('My Title');
        $fixture->setPtsExperience('My Title');
        $fixture->setPrime('My Title');
        $fixture->setADiplome('My Title');
        $fixture->setNationnalite('My Title');
        $fixture->setTypeNature('My Title');
        $fixture->setTypeReferentiel('My Title');
        $fixture->setDejaComplete('My Title');
        $fixture->setSupprime('My Title');
        $fixture->setDateSuppression('My Title');
        $fixture->setNumeroAgentManager('My Title');
        $fixture->setCle('My Title');
        $fixture->setLien('My Title');
        $fixture->setTypeCandidat('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Candidat');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Candidat();
        $fixture->setNom('My Title');
        $fixture->setPrenom('My Title');
        $fixture->setAdresse('My Title');
        $fixture->setCodePostal('My Title');
        $fixture->setVille('My Title');
        $fixture->setDateDeNaissance('My Title');
        $fixture->setVilleNaissance('My Title');
        $fixture->setDepartementNaissance('My Title');
        $fixture->setPaysNaissance('My Title');
        $fixture->setNumeroSs('My Title');
        $fixture->setNomUsage('My Title');
        $fixture->setCompletementAdresse('My Title');
        $fixture->setDateExpirationTs('My Title');
        $fixture->setSexe('My Title');
        $fixture->setEmail('My Title');
        $fixture->setDatePrevisEmbauche('My Title');
        $fixture->setPoste('My Title');
        $fixture->setSite('My Title');
        $fixture->setDelaiFormulaire('My Title');
        $fixture->setMdp('My Title');
        $fixture->setNumeroAgent('My Title');
        $fixture->setDebutCDD('My Title');
        $fixture->setFinCDD('My Title');
        $fixture->setService('My Title');
        $fixture->setCoeffDeveloppe('My Title');
        $fixture->setPtsGarantie('My Title');
        $fixture->setNiveauSalaire('My Title');
        $fixture->setCoeffBase('My Title');
        $fixture->setPtsCompetences('My Title');
        $fixture->setPeriodeEssai('My Title');
        $fixture->setPtsExperience('My Title');
        $fixture->setPrime('My Title');
        $fixture->setADiplome('My Title');
        $fixture->setNationnalite('My Title');
        $fixture->setTypeNature('My Title');
        $fixture->setTypeReferentiel('My Title');
        $fixture->setDejaComplete('My Title');
        $fixture->setSupprime('My Title');
        $fixture->setDateSuppression('My Title');
        $fixture->setNumeroAgentManager('My Title');
        $fixture->setCle('My Title');
        $fixture->setLien('My Title');
        $fixture->setTypeCandidat('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'candidat[nom]' => 'Something New',
            'candidat[prenom]' => 'Something New',
            'candidat[adresse]' => 'Something New',
            'candidat[codePostal]' => 'Something New',
            'candidat[ville]' => 'Something New',
            'candidat[dateDeNaissance]' => 'Something New',
            'candidat[villeNaissance]' => 'Something New',
            'candidat[departementNaissance]' => 'Something New',
            'candidat[paysNaissance]' => 'Something New',
            'candidat[numeroSs]' => 'Something New',
            'candidat[nomUsage]' => 'Something New',
            'candidat[completementAdresse]' => 'Something New',
            'candidat[dateExpirationTs]' => 'Something New',
            'candidat[sexe]' => 'Something New',
            'candidat[email]' => 'Something New',
            'candidat[datePrevisEmbauche]' => 'Something New',
            'candidat[poste]' => 'Something New',
            'candidat[site]' => 'Something New',
            'candidat[delaiFormulaire]' => 'Something New',
            'candidat[mdp]' => 'Something New',
            'candidat[numeroAgent]' => 'Something New',
            'candidat[debutCDD]' => 'Something New',
            'candidat[finCDD]' => 'Something New',
            'candidat[service]' => 'Something New',
            'candidat[coeffDeveloppe]' => 'Something New',
            'candidat[ptsGarantie]' => 'Something New',
            'candidat[niveauSalaire]' => 'Something New',
            'candidat[coeffBase]' => 'Something New',
            'candidat[ptsCompetences]' => 'Something New',
            'candidat[periodeEssai]' => 'Something New',
            'candidat[ptsExperience]' => 'Something New',
            'candidat[prime]' => 'Something New',
            'candidat[aDiplome]' => 'Something New',
            'candidat[nationnalite]' => 'Something New',
            'candidat[typeNature]' => 'Something New',
            'candidat[typeReferentiel]' => 'Something New',
            'candidat[dejaComplete]' => 'Something New',
            'candidat[supprime]' => 'Something New',
            'candidat[dateSuppression]' => 'Something New',
            'candidat[numeroAgentManager]' => 'Something New',
            'candidat[cle]' => 'Something New',
            'candidat[lien]' => 'Something New',
            'candidat[typeCandidat]' => 'Something New',
        ]);

        self::assertResponseRedirects('/candidat/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getPrenom());
        self::assertSame('Something New', $fixture[0]->getAdresse());
        self::assertSame('Something New', $fixture[0]->getCodePostal());
        self::assertSame('Something New', $fixture[0]->getVille());
        self::assertSame('Something New', $fixture[0]->getDateDeNaissance());
        self::assertSame('Something New', $fixture[0]->getVilleNaissance());
        self::assertSame('Something New', $fixture[0]->getDepartementNaissance());
        self::assertSame('Something New', $fixture[0]->getPaysNaissance());
        self::assertSame('Something New', $fixture[0]->getNumeroSs());
        self::assertSame('Something New', $fixture[0]->getNomUsage());
        self::assertSame('Something New', $fixture[0]->getCompletementAdresse());
        self::assertSame('Something New', $fixture[0]->getDateExpirationTs());
        self::assertSame('Something New', $fixture[0]->getSexe());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getDatePrevisEmbauche());
        self::assertSame('Something New', $fixture[0]->getPoste());
        self::assertSame('Something New', $fixture[0]->getSite());
        self::assertSame('Something New', $fixture[0]->getDelaiFormulaire());
        self::assertSame('Something New', $fixture[0]->getMdp());
        self::assertSame('Something New', $fixture[0]->getNumeroAgent());
        self::assertSame('Something New', $fixture[0]->getDebutCDD());
        self::assertSame('Something New', $fixture[0]->getFinCDD());
        self::assertSame('Something New', $fixture[0]->getService());
        self::assertSame('Something New', $fixture[0]->getCoeffDeveloppe());
        self::assertSame('Something New', $fixture[0]->getPtsGarantie());
        self::assertSame('Something New', $fixture[0]->getNiveauSalaire());
        self::assertSame('Something New', $fixture[0]->getCoeffBase());
        self::assertSame('Something New', $fixture[0]->getPtsCompetences());
        self::assertSame('Something New', $fixture[0]->getPeriodeEssai());
        self::assertSame('Something New', $fixture[0]->getPtsExperience());
        self::assertSame('Something New', $fixture[0]->getPrime());
        self::assertSame('Something New', $fixture[0]->getADiplome());
        self::assertSame('Something New', $fixture[0]->getNationnalite());
        self::assertSame('Something New', $fixture[0]->getTypeNature());
        self::assertSame('Something New', $fixture[0]->getTypeReferentiel());
        self::assertSame('Something New', $fixture[0]->getDejaComplete());
        self::assertSame('Something New', $fixture[0]->getSupprime());
        self::assertSame('Something New', $fixture[0]->getDateSuppression());
        self::assertSame('Something New', $fixture[0]->getNumeroAgentManager());
        self::assertSame('Something New', $fixture[0]->getCle());
        self::assertSame('Something New', $fixture[0]->getLien());
        self::assertSame('Something New', $fixture[0]->getTypeCandidat());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Candidat();
        $fixture->setNom('My Title');
        $fixture->setPrenom('My Title');
        $fixture->setAdresse('My Title');
        $fixture->setCodePostal('My Title');
        $fixture->setVille('My Title');
        $fixture->setDateDeNaissance('My Title');
        $fixture->setVilleNaissance('My Title');
        $fixture->setDepartementNaissance('My Title');
        $fixture->setPaysNaissance('My Title');
        $fixture->setNumeroSs('My Title');
        $fixture->setNomUsage('My Title');
        $fixture->setCompletementAdresse('My Title');
        $fixture->setDateExpirationTs('My Title');
        $fixture->setSexe('My Title');
        $fixture->setEmail('My Title');
        $fixture->setDatePrevisEmbauche('My Title');
        $fixture->setPoste('My Title');
        $fixture->setSite('My Title');
        $fixture->setDelaiFormulaire('My Title');
        $fixture->setMdp('My Title');
        $fixture->setNumeroAgent('My Title');
        $fixture->setDebutCDD('My Title');
        $fixture->setFinCDD('My Title');
        $fixture->setService('My Title');
        $fixture->setCoeffDeveloppe('My Title');
        $fixture->setPtsGarantie('My Title');
        $fixture->setNiveauSalaire('My Title');
        $fixture->setCoeffBase('My Title');
        $fixture->setPtsCompetences('My Title');
        $fixture->setPeriodeEssai('My Title');
        $fixture->setPtsExperience('My Title');
        $fixture->setPrime('My Title');
        $fixture->setADiplome('My Title');
        $fixture->setNationnalite('My Title');
        $fixture->setTypeNature('My Title');
        $fixture->setTypeReferentiel('My Title');
        $fixture->setDejaComplete('My Title');
        $fixture->setSupprime('My Title');
        $fixture->setDateSuppression('My Title');
        $fixture->setNumeroAgentManager('My Title');
        $fixture->setCle('My Title');
        $fixture->setLien('My Title');
        $fixture->setTypeCandidat('My Title');

        $this->repository->add($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/candidat/');
    }
}
