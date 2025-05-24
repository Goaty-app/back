<?php

namespace App\Serializer\Normalizer;

use App\Entity\Animal;
use App\Entity\AnimalType;
use App\Entity\Breeding;
use App\Entity\FoodStock;
use App\Entity\FoodStockHistory;
use App\Entity\FoodStockType;
use App\Entity\Healthcare;
use App\Entity\HealthcareType;
use App\Entity\Herd;
use App\Entity\Production;
use App\Entity\ProductionType;
use Exception;
use ReflectionClass;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AutoDiscoveryNormalizer implements NormalizerInterface
{
    private const ENTITIES_WITH_HERD = [
        Production::class,
        Animal::class,
        FoodStock::class,
    ];

    private const ENTITIES_WITH_FOOD_STOCK = [
        FoodStockHistory::class,
    ];

    private const ENTITIES_WITH_ANIMAL = [
        Healthcare::class,
        Breeding::class,
    ];

    private array $entityToCamelCreate = [];

    public function __construct(
        #[Autowire(service: 'serializer.normalizer.object')]
        private NormalizerInterface $normalizer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
        $this->entityToCamelCreate = [
            ...array_fill_keys(self::ENTITIES_WITH_HERD, 'herd'),
            ...array_fill_keys(self::ENTITIES_WITH_FOOD_STOCK, 'foodStock'),
            ...array_fill_keys(self::ENTITIES_WITH_ANIMAL, 'animal'),
        ];
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $data = $this->normalizer->normalize($object, $format, $context);
        $classShortName = (new ReflectionClass($object))->getShortName();
        $classSnake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $classShortName));
        $classCamel = lcfirst($classShortName);

        $classCamelCreate = $this->getEntitySpecificParams($object, $data);
        $data['_links'] = $this->generateLinks($classSnake, $classCamel, $data['id'], $classCamelCreate);

        return $data;
    }

    private function getEntitySpecificParams(object $object, array $data): array
    {
        $reflector = new ReflectionClass($object);
        $parentClassReflector = $reflector->getParentClass();

        $entityClass = $parentClassReflector ? $parentClassReflector->getName() : $reflector->getName();

        if (\array_key_exists($entityClass, $this->entityToCamelCreate)) {
            $paramName = $this->entityToCamelCreate[$entityClass];

            return [
                $paramName => $data['id'],
            ];
        }

        return [];
    }

    private function generateLinks(string $classSnake, string $classCamel, int $id, array $classCamelCreate): array
    {
        $links = [];

        $links['self'] = $this->generateLink(
            "api_{$classSnake}_get",
            [$classCamel => $id],
            ['GET'],
        );
        $links['up_in'] = $this->generateLink(
            "api_{$classSnake}_get_all_in",
            $classCamelCreate,
            ['GET'],
        );
        $links['up'] = $this->generateLink(
            "api_{$classSnake}_get_all",
            [],
            ['GET'],
        );
        $links['create'] = $this->generateLink(
            "api_{$classSnake}_create",
            $classCamelCreate,
            ['POST'],
        );
        $links['update'] = $this->generateLink(
            "api_{$classSnake}_update",
            [$classCamel => $id],
            ['PATCH'],
        );
        $links['delete'] = $this->generateLink(
            "api_{$classSnake}_delete",
            [$classCamel => $id],
            ['DELETE'],
        );

        // Remove everythig after '?' in the href
        foreach (['create', 'update', 'delete'] as $key) {
            if (isset($links[$key]['href']) && \is_string($links[$key]['href'])) {
                $links[$key]['href'] = explode('?', $links[$key]['href'], 2)[0];
            }
        }

        return array_filter($links);
    }

    private function generateLink(string $route, array $params, array $methods): ?array
    {
        try {
            return [
                'href' => $this->urlGenerator->generate(
                    $route,
                    $params,
                    UrlGeneratorInterface::ABSOLUTE_URL,
                ),
                'methods' => $methods,
            ];
        } catch (Exception) {
            return null;
        }
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $this->supportsType($data) && 'json' === $format && !isset($context['autodiscovery_applied']);
    }

    private function supportsType($data): bool
    {
        return $data instanceof Herd
            || $data instanceof Animal
            || $data instanceof AnimalType
            || $data instanceof Production
            || $data instanceof ProductionType
            || $data instanceof FoodStock
            || $data instanceof FoodStockType
            || $data instanceof FoodStockHistory
            || $data instanceof Healthcare
            || $data instanceof HealthcareType
            || $data instanceof Breeding;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Herd::class             => true,
            Animal::class           => true,
            AnimalType::class       => true,
            Production::class       => true,
            ProductionType::class   => true,
            FoodStock::class        => true,
            FoodStockType::class    => true,
            FoodStockHistory::class => true,
            Healthcare::class       => true,
            HealthcareType::class   => true,
            Breeding::class         => true,
        ];
    }
}
