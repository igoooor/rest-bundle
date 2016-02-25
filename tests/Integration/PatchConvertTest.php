<?php
namespace Ibrows\RestBundle\Tests\Integration;

use Ibrows\RestBundle\Patch\Address\ObjectAddress;
use Ibrows\RestBundle\Patch\ExecutionerInterface;
use Ibrows\RestBundle\Patch\OperationInterface;
use Ibrows\RestBundle\Patch\PatchConverterInterface;
use Ibrows\RestBundle\Tests\Integration\Entity\Article;
use Ibrows\RestBundle\Tests\Integration\Entity\Comment;

class PatchConvertTest extends WebTestCase
{
    /**
     * @return PatchConverterInterface
     */
    private function getPatchConverter()
    {
        return $this->getContainer()->get('ibrows_rest.patch.patch_converter');
    }

    /**
     * @return ExecutionerInterface
     */
    private function getExecutioner()
    {
        return $this->getContainer()->get('ibrows_rest.patch.executioner');
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
                    'from' => '/small~1list/2',
                    'path' => '/small~1list/1',
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\ResolvePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\ResolvePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\ResolvePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\ResolvePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\OverridePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\ResolvePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\ResolvePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\OperationInvalidException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\OperationInvalidException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\ResolvePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\ResolvePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\OverridePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\ResolvePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\ResolvePathException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\OverridePathException
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

        static::assertEquals('/subject', $operation->pathPointer()->resolve(new Comment())->pointer()->path());
        static::assertInstanceOf(ObjectAddress::class, $operation->pathPointer()->resolve(new Comment())->parent());
        static::assertNull($operation->pathPointer()->resolve(new Comment())->parent()->parent());
    }

    /**
     * @expectedException \Ibrows\RestBundle\Patch\Exception\OperationInvalidException
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
     * @expectedException \Ibrows\RestBundle\Patch\Exception\OperationInvalidException
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
