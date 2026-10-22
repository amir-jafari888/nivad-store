<?php

declare(strict_types=1);

final class SliderService
{
    private SliderRepository $repository;

    public function __construct(
        ?SliderRepository $repository = null
    ) {
        $this->repository =
            $repository ??
            new SliderRepository();
    }

    public function findById(
        int $sliderId
    ): ?Slider {
        return $this->repository->findById(
            $sliderId
        );
    }

    public function findBySlug(
        string $slug
    ): ?Slider {
        return $this->repository->findBySlug(
            $slug
        );
    }

    public function findAll(): array
    {
        return $this->repository->findAll();
    }

    public function create(
        string $name,
        string $slug,
        ?string $description = null,
        string $status = Slider::STATUS_ACTIVE,
        int $sortOrder = 0,
        ?string $startsAt = null,
        ?string $endsAt = null
    ): ?Slider {
        return $this->repository->create(
            $name,
            $slug,
            $description,
            $status,
            $sortOrder,
            $startsAt,
            $endsAt
        );
    }

    public function update(
        int $sliderId,
        string $name,
        string $slug,
        ?string $description = null,
        string $status = Slider::STATUS_ACTIVE,
        int $sortOrder = 0,
        ?string $startsAt = null,
        ?string $endsAt = null
    ): ?Slider {
        return $this->repository->update(
            $sliderId,
            $name,
            $slug,
            $description,
            $status,
            $sortOrder,
            $startsAt,
            $endsAt
        );
    }

    public function delete(
        int $sliderId
    ): bool {
        return $this->repository->delete(
            $sliderId
        );
    }

    public function restore(
        int $sliderId
    ): ?Slider {
        return $this->repository->restore(
            $sliderId
        );
    }

    public function updateStatus(
        int $sliderId,
        string $status
    ): ?Slider {
        $allowedStatuses = [
            Slider::STATUS_ACTIVE,
            Slider::STATUS_INACTIVE,
        ];

        if (
            !in_array(
                $status,
                $allowedStatuses,
                true
            )
        ) {
            return null;
        }

        return $this->repository->updateStatus(
            $sliderId,
            $status
        );
    }

    public function findActiveById(
        int $sliderId
    ): ?Slider {
        $slider =
            $this->repository->findActiveById(
                $sliderId
            );

        if (
            $slider === null ||
            !$this->isCurrentlyAvailable(
                $slider
            )
        ) {
            return null;
        }

        return $slider;
    }

    public function findActive(): array
    {
        return array_values(
            array_filter(
                $this->repository->findActive(),
                fn(Slider $slider): bool =>
                $this->isCurrentlyAvailable(
                    $slider
                )
            )
        );
    }

    public function findSlidesBySliderId(
        int $sliderId
    ): array {
        return $this->repository
            ->findSlidesBySliderId(
                $sliderId
            );
    }

    public function findActiveSlidesBySliderId(
        int $sliderId
    ): array {
        return array_values(
            array_filter(
                $this->repository
                    ->findActiveSlidesBySliderId(
                        $sliderId
                    ),
                fn(Slide $slide): bool =>
                $this->isSlideCurrentlyAvailable(
                    $slide
                )
            )
        );
    }

    public function isCurrentlyAvailable(
        Slider $slider
    ): bool {
        if (
            $slider->status !==
            Slider::STATUS_ACTIVE
        ) {
            return false;
        }

        $now = time();

        if ($slider->startsAt !== null) {
            $startsAt =
                strtotime($slider->startsAt);

            if (
                $startsAt !== false &&
                $now < $startsAt
            ) {
                return false;
            }
        }

        if ($slider->endsAt !== null) {
            $endsAt =
                strtotime($slider->endsAt);

            if (
                $endsAt !== false &&
                $now > $endsAt
            ) {
                return false;
            }
        }

        return true;
    }

    private function isSlideCurrentlyAvailable(
        Slide $slide
    ): bool {
        if (
            $slide->status !==
            Slide::STATUS_ACTIVE
        ) {
            return false;
        }

        $now = time();

        if ($slide->startsAt !== null) {
            $startsAt =
                strtotime($slide->startsAt);

            if (
                $startsAt !== false &&
                $now < $startsAt
            ) {
                return false;
            }
        }

        if ($slide->endsAt !== null) {
            $endsAt =
                strtotime($slide->endsAt);

            if (
                $endsAt !== false &&
                $now > $endsAt
            ) {
                return false;
            }
        }

        return true;
    }
}
