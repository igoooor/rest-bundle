<?php
namespace Ibrows\RestBundle\ParamConverter;

use Doctrine\Common\Collections\Collection;
use Ibrows\RestBundle\Request\LinkHeader;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LinkParamConverter extends AbstractLinkParamConverter
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'link';
    }

    /**
     * {@inheritdoc}
     */
    protected function applyLink(LinkHeader $link, $object, array $allowedRelations)
    {
        $this->checkRelation($link, $allowedRelations);

        $entityToLink = $link->getResource();

        /** @var Collection $collection */
        $collection = $object->{'get' . ucfirst($link->getRelation())}();

        if(!$entityToLink) {
            throw new NotFoundHttpException;
        }

        if($collection->contains($entityToLink)) {
            throw new ConflictHttpException('Entity is already linked to ' . $entityToLink->getId());
        }

        $collection->add($entityToLink);
    }
}