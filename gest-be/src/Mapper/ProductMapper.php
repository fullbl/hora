<?php

namespace App\Mapper;

use App\Entity\Product;
use App\Entity\Step;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ProductMapper
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $em
    ) {
    }

    public function fromRequest(Request $request): Product
    {
        return $this->fill(new Product(), $request);
    }

    public function fill(Product $product, Request $request): Product
    {
        $newProduct = $this->serializer->deserialize(
            $request->getContent(),
            Product::class,
            'json',
            [
                AbstractNormalizer::OBJECT_TO_POPULATE => $product,
                AbstractNormalizer::GROUPS => ['product-edit'],
            ]
        );
        $data = $request->toArray();
        foreach ($data['steps'] as $stepData) {
            if (isset($stepData['id'])) {
                $step = $this->em->find(Step::class, $stepData['id']);
                $step = $this->serializer->deserialize(
                    json_encode($stepData),
                    Step::class,
                    'json',
                    [
                        AbstractNormalizer::OBJECT_TO_POPULATE => $step,
                    ]
                );
            } else {
                $step = $this->serializer->deserialize(
                    json_encode($stepData),
                    Step::class,
                    'json'
                );
            }
            $newProduct->addStep($step);
        }

        return $newProduct;
    }
}
