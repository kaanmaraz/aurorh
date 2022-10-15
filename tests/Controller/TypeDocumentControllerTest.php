<?php

namespace App\Test\Controller;

use App\Entity\TypeDocument;
use App\Repository\TypeDocumentRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TypeDocumentControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private TypeDocumentRepository $repository;
    private string $path = '/type/document/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(TypeDocument::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('TypeDocument index');

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
            'type_document[libelle]' => 'Testing',
            'type_document[obligatoire]' => 'Testing',
            'type_document[multiple]' => 'Testing',
            'type_document[format]' => 'Testing',
            'type_document[typeCandidats]' => 'Testing',
            'type_document[documents]' => 'Testing',
        ]);

        self::assertResponseRedirects('/type/document/');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new TypeDocument();
        $fixture->setLibelle('My Title');
        $fixture->setObligatoire('My Title');
        $fixture->setMultiple('My Title');
        $fixture->setFormat('My Title');
        $fixture->setTypeCandidats('My Title');
        $fixture->setDocuments('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('TypeDocument');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new TypeDocument();
        $fixture->setLibelle('My Title');
        $fixture->setObligatoire('My Title');
        $fixture->setMultiple('My Title');
        $fixture->setFormat('My Title');
        $fixture->setTypeCandidats('My Title');
        $fixture->setDocuments('My Title');

        $this->repository->add($fixture, true);

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'type_document[libelle]' => 'Something New',
            'type_document[obligatoire]' => 'Something New',
            'type_document[multiple]' => 'Something New',
            'type_document[format]' => 'Something New',
            'type_document[typeCandidats]' => 'Something New',
            'type_document[documents]' => 'Something New',
        ]);

        self::assertResponseRedirects('/type/document/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getLibelle());
        self::assertSame('Something New', $fixture[0]->getObligatoire());
        self::assertSame('Something New', $fixture[0]->getMultiple());
        self::assertSame('Something New', $fixture[0]->getFormat());
        self::assertSame('Something New', $fixture[0]->getTypeCandidats());
        self::assertSame('Something New', $fixture[0]->getDocuments());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new TypeDocument();
        $fixture->setLibelle('My Title');
        $fixture->setObligatoire('My Title');
        $fixture->setMultiple('My Title');
        $fixture->setFormat('My Title');
        $fixture->setTypeCandidats('My Title');
        $fixture->setDocuments('My Title');

        $this->repository->add($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
        self::assertResponseRedirects('/type/document/');
    }
}
