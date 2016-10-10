<?php
namespace Ibrows\RestBundle\Tests\Integration;

use DateTime;
use Ibrows\JsonPatch\ExecutionerInterface;
use Ibrows\JsonPatch\PatchConverterInterface;
use Ibrows\RestBundle\Tests\Integration\Entity\Article;
use Ibrows\RestBundle\Tests\Integration\Entity\Comment;
use JMS\Serializer\DeserializationContext;

class JMSPatchConvertTest extends WebTestCase
{
    /**
     * @return PatchConverterInterface
     */
    private function getPatchConverter()
    {
        return $this->getContainer()->get('ibrows_json_patch.patch_converter');
    }

    /**
     * @return ExecutionerInterface
     */
    private function getExecutioner()
    {
        return $this->getContainer()->get('ibrows_json_patch.executioner.jms');
    }

    public function testJMSValue()
    {
        $date = '2012-01-01T01:00:00+01:00';
        $article = new Article();
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'add',
                    'path'  => '/someDate',
                    'value' => $date,
                ],
            ]
        );

        /** @var Article $response */
        $response = $this->getExecutioner()->execute($operations, $article);
        static::assertInstanceOf(Article::class, $response);
        static::assertInstanceOf(DateTime::class, $response->getSomeDate());
        static::assertEquals($date, $response->getSomeDate()->format('c'));
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "invalid" on current address.
     */
    public function testInvalidJMSValue()
    {
        $article = new Article();
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'add',
                    'path'  => '/invalid',
                    'value' => 'something',
                ],
            ]
        );

        $this->getExecutioner()->execute($operations, $article);
    }

    public function testJMSValueConverterOnArray()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'add',
                    'path'  => '/invalid',
                    'value' => 'something',
                ],
            ]
        );

        $this->getExecutioner()->execute($operations, []);
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "subject" on current address.
     */
    public function testJMSGroups()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'replace',
                    'path'  => '/subject',
                    'value' => 'something',
                ],
            ]
        );

        $comment = new Comment();
        $comment->setSubject('subject before');

        $context = DeserializationContext::create();
        $context->setGroups(
            [
                'emptyGroup',
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            $comment,
            [
                'jms_context' => $context,
            ]
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "hidden" on current address.
     */
    public function testReadOnly()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'add',
                    'path'  => '/hidden',
                    'value' => 'something',
                ],
            ]
        );

        $comment = new Comment();

        $this->getExecutioner()->execute($operations, $comment);
    }

    public function testSerializedName()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'add',
                    'path'  => '/fakeName',
                    'value' => 'something',
                ],
            ]
        );

        $comment = new Comment();

        $this->getExecutioner()->execute($operations, $comment);
    }
}
