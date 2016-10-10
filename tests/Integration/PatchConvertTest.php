<?php
namespace Ibrows\RestBundle\Tests\Integration;

use Ibrows\JsonPatch\Address\ObjectAddress;
use Ibrows\JsonPatch\ExecutionerInterface;
use Ibrows\JsonPatch\OperationInterface;
use Ibrows\JsonPatch\PatchConverterInterface;
use Ibrows\RestBundle\Tests\Integration\Entity\Article;
use Ibrows\RestBundle\Tests\Integration\Entity\Comment;

class PatchConvertTest extends WebTestCase
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
        return $this->getContainer()->get('ibrows_json_patch.executioner');
    }

    public function testPatchList()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'add',
                    'path'  => '/small~1list/-',
                    'value' => 'list item 2',
                ],
                [
                    'op'    => 'add',
                    'path'  => '/small~1list/0',
                    'value' => 'list item 1',
                ],
                [
                    'op'    => 'add',
                    'path'  => '/small~1list/-',
                    'value' => 'list item 3',
                ],
                [
                    'op'   => 'remove',
                    'path' => '/small~1list/2',
                ],
                [
                    'op'   => 'move',
                    'from' => '/small~1list/1',
                    'path' => '/small~1list/0',
                ],
                [
                    'op'   => 'copy',
                    'from' => '/small~1list/0',
                    'path' => '/small~1list/-',
                ],
                [
                    'op'   => 'remove',
                    'path' => '/small~1list/0',
                ],
                [
                    'op'    => 'test',
                    'path'  => '/small~1list/0',
                    'value' => 'list item 1',
                ],
                [
                    'op'    => 'add',
                    'path'  => '/small~1list/-',
                    'value' => 'list item 77',
                ],
                [
                    'op'   => 'move',
                    'path' => '/small~1list/1',
                    'from' => '/small~1list/2',
                ],
                [
                    'op'    => 'replace',
                    'path'  => '/small~1list/1',
                    'value' => 'list item 1.5',
                ],
            ]
        );

        $object = $this->getExecutioner()->execute(
            $operations,
            [
                'small/list' => [],
            ]
        );

        static::assertEquals(
            [
                'small/list' => [
                    'list item 1',
                    'list item 1.5',
                    'list item 2',
                ],
            ],
            $object
        );
    }

    public function testHashSet()
    {

        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'replace',
                    'path'  => '/some~0/path',
                    'value' => 'something else',
                ],
                [
                    'op'    => 'test',
                    'path'  => '/some~0/path',
                    'value' => 'something else',
                ],
                [
                    'op'   => 'remove',
                    'path' => '/some~0',
                ],
                [
                    'op'    => 'replace',
                    'path'  => '/listToValue',
                    'value' => 'value',
                ],
                [
                    'op'    => 'add',
                    'path'  => '/some~1',
                    'value' => 42,
                ],
            ]
        );

        $object = $this->getExecutioner()->execute(
            $operations,
            [
                'some~'       => [
                    'path' => 'something',
                ],
                'listToValue' => [],
            ]
        );

        static::assertEquals(
            [
                'listToValue' => 'value',
                'some/'       => 42,
            ],
            $object
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "path" on current address.
     */
    public function testMissingPathHashSet()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'replace',
                    'path'  => '/missing/path',
                    'value' => 'something else',
                ],
            ]
        );
        $this->getExecutioner()->execute(
            $operations,
            [
                'some' => [
                    'hash',
                    'set',
                ],
            ]
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "7" on current address.
     */
    public function testReplaceMissingPathList()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'replace',
                    'path'  => '/7',
                    'value' => 'something else',
                ],
            ]
        );
        $this->getExecutioner()->execute(
            $operations,
            []
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "77" on current address.
     */
    public function testMissingPathList()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'replace',
                    'path'  => '/7/77',
                    'value' => 'something else',
                ],
            ]
        );
        $this->getExecutioner()->execute(
            $operations,
            []
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\OperationInvalidException
     * @expectedExceptionMessage The property "path" must be provided for every operation.
     */
    public function testMissingPath()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'replace',
                    'value' => 'something else',
                ],
            ]
        );
        $this->getExecutioner()->execute(
            $operations,
            []
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "missing" on current address.
     */
    public function testReplaceMissingPathHashSet()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'replace',
                    'path'  => '/missing',
                    'value' => 'something else',
                ],
            ]
        );
        $this->getExecutioner()->execute(
            $operations,
            [
                'hash' => 'set',
            ]
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\OverridePathException
     * @expectedExceptionMessage Could not add on path "override" because it already exists.
     */
    public function testAddExistingPathHashSet()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'add',
                    'path'  => '/override',
                    'value' => 'something else',
                ],
            ]
        );
        $this->getExecutioner()->execute(
            $operations,
            [
                'override' => 'something',
            ]
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "missing" on current address.
     */
    public function testRemoveMissingPathHashSet()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'   => 'remove',
                    'path' => '/missing',
                ],
            ]
        );
        $this->getExecutioner()->execute(
            $operations,
            [
                'hash' => 'set',
            ]
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "3" on current address.
     */
    public function testRemoveMissingPathList()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'   => 'remove',
                    'path' => '/3',
                ],
            ]
        );
        $this->getExecutioner()->execute(
            $operations,
            [
                '1',
                '2',
            ]
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\OperationInvalidException
     * @expectedExceptionMessage Operation test failed. Expected: "something", Actual: "something else"
     */
    public function testFailingTestOperation()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'test',
                    'path'  => '/path',
                    'value' => 'something else',
                ],
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            [
                'path' => 'something',
            ]
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\OperationInvalidException
     * @expectedExceptionMessage Couldn't find an applier for the operation "something".
     */
    public function testMissingOperation()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'   => 'something',
                    'path' => '/path',
                ],
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            []
        );
    }

    public function testObject()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'replace',
                    'path'  => '/subject',
                    'value' => 'new subject',
                ],
                [
                    'op'    => 'add',
                    'path'  => '/message',
                    'value' => 'message',
                ],
                [
                    'op'    => 'test',
                    'path'  => '/message',
                    'value' => 'message',
                ],
                [
                    'op'   => 'remove',
                    'path' => '/message',
                ],
                [
                    'op'   => 'move',
                    'path' => '/message',
                    'from' => '/subject',
                ],
                [
                    'op'    => 'test',
                    'path'  => '/subject',
                    'value' => '',
                ],
                [
                    'op'    => 'replace',
                    'path'  => '/message',
                    'value' => 'some new value',
                ],
                [
                    'op'   => 'copy',
                    'path' => '/subject',
                    'from' => '/message',
                ],
                [
                    'op'    => 'replace',
                    'path'  => '/article/title',
                    'value' => 'new test title',
                ],
            ]
        );

        $comment = new Comment();
        $comment->setSubject('old subject');
        $article = new Article();
        $article->setTitle('test title');
        $comment->setArticle($article);

        $this->getExecutioner()->execute(
            $operations,
            $comment
        );

        static::assertEquals('some new value', $comment->getSubject());
        static::assertEquals('some new value', $comment->getMessage());
        static::assertEquals('new test title', $comment->getArticle()->getTitle());
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "some" on current address.
     */
    public function testInvalidPathDirectlyOnObject()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'replace',
                    'path'  => '/some',
                    'value' => 'new subject',
                ],
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            new Comment()
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "missing" on current address.
     */
    public function testInvalidPathOnObject()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'replace',
                    'path'  => '/some/missing',
                    'value' => 'new subject',
                ],
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            new Comment()
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\OperationInvalidException
     */
    public function testInvalidPatchBody()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                'message' => "blabla"
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            new Comment()
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\OverridePathException
     * @expectedExceptionMessage Could not add on path "subject" because it already exists.
     */
    public function testNotNullAddOnObject()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'add',
                    'path'  => '/subject',
                    'value' => 'new subject',
                ],
            ]
        );

        $comment = new Comment();
        $comment->setSubject('can\'t add here');

        $this->getExecutioner()->execute(
            $operations,
            $comment
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "missing" on current address.
     */
    public function testInvalidAddOnObject()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'add',
                    'path'  => '/missing',
                    'value' => 'new subject',
                ],
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            new Comment()
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\ResolvePathException
     * @expectedExceptionMessage Could not resolve path "missing" on current address.
     */
    public function testInvalidRemoveOnObject()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'   => 'remove',
                    'path' => '/missing',
                ],
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            new Comment()
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\PropertyNullPathException
     * @expectedExceptionMessage Could not change on path "subject" because value is null ( exists not ).
     */
    public function testInvalidNullRemoveOnObject()
    {
        $comment = new Comment();
        $comment->setSubject(null);
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'   => 'remove',
                    'path' => '/subject',
                ],
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            $comment
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\PropertyNullPathException
     * @expectedExceptionMessage Could not change on path "subject" because value is null ( exists not ).
     */
    public function testInvalidNullReplaceOnObject()
    {
        $comment = new Comment();
        $comment->setSubject(null);
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'   => 'replace',
                    'path' => '/subject',
                    'value' => 'new'
                ],
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            $comment
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\OverridePathException
     * @expectedExceptionMessage Could not add on path "subject" because it already exists.
     */
    public function testOverrideObject()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'add',
                    'path'  => '/subject',
                    'value' => 'new subject',
                ],
            ]
        );

        $comment = new Comment();
        $comment->setSubject('old subject');

        $this->getExecutioner()->execute(
            $operations,
            $comment
        );
    }

    public function testPointers()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'    => 'add',
                    'path'  => '/subject',
                    'value' => 'new subject',
                ],
            ]
        );

        /** @var OperationInterface $operation */
        $operation = array_shift($operations);

        $comment = new Comment();

        $value = $this->getContainer()->get('ibrows_json_patch.address_lookup')->lookup(
            $operation->pathPointer(),
            $comment
        );

        static::assertEquals('/subject', $value->pointer()->path());
        static::assertInstanceOf(ObjectAddress::class, $value->parent());
        static::assertNull($value->parent()->parent());
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\OperationInvalidException
     * @expectedExceptionMessage The property "from" must be provided for the move operation.
     */
    public function testMoveWithoutFrom()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'   => 'move',
                    'path' => '/subject',
                ],
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            []
        );
    }

    /**
     * @expectedException \Ibrows\JsonPatch\Exception\OperationInvalidException
     * @expectedExceptionMessage The property "from" must be provided for the copy operation.
     */
    public function testCopyWithoutFrom()
    {
        $operations = $this->getPatchConverter()->convert(
            [
                [
                    'op'   => 'copy',
                    'path' => '/subject',
                ],
            ]
        );

        $this->getExecutioner()->execute(
            $operations,
            []
        );
    }
}
