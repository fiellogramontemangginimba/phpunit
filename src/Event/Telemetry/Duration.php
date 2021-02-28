<?php declare(strict_types=1);
/*
 * This file is part of PHPUnit.
 *
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PHPUnit\Event\Telemetry;

use function floor;
use function sprintf;
use InvalidArgumentException;

final class Duration
{
    private int $seconds;

    private int $nanoseconds;

    /**
     * @throws InvalidArgumentException
     */
    public static function fromSecondsAndNanoseconds(int $seconds, int $nanoseconds): self
    {
        return new self(
            $seconds,
            $nanoseconds
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function __construct(int $seconds, int $nanoseconds)
    {
        $this->ensureNotNegativeInt($seconds, 'seconds');
        $this->ensureNotNegativeInt($nanoseconds, 'nanoseconds');
        $this->ensureNotGreaterThan(999999999, $nanoseconds, 'nanoseconds');

        $this->seconds     = $seconds;
        $this->nanoseconds = $nanoseconds;
    }

    public function seconds(): int
    {
        return $this->seconds;
    }

    public function nanoseconds(): int
    {
        return $this->nanoseconds;
    }

    public function asString(DurationFormatter $formatter = null): string
    {
        if ($formatter !== null) {
            return $formatter->format($this);
        }

        $seconds = $this->seconds();
        $minutes = 00;
        $hours   = 00;

        if ($seconds > 60 * 60) {
            $hours = floor($seconds / 60 / 60);
            $seconds -= ($hours * 60 * 60);
        }

        if ($seconds > 60) {
            $minutes = floor($seconds / 60);
            $seconds -= ($minutes * 60);
        }

        return sprintf(
            '%02d:%02d:%02d.%09d',
            $hours,
            $minutes,
            $seconds,
            $this->nanoseconds()
        );
    }

    public function equals(self $other): bool
    {
        return $this->seconds === $other->seconds &&
            $this->nanoseconds === $other->nanoseconds;
    }

    public function isLessThan(self $other): bool
    {
        if ($this->seconds < $other->seconds) {
            return true;
        }

        if ($this->seconds > $other->seconds) {
            return false;
        }

        return $this->nanoseconds < $other->nanoseconds;
    }

    public function isGreaterThan(self $other): bool
    {
        if ($this->seconds > $other->seconds) {
            return true;
        }

        if ($this->seconds < $other->seconds) {
            return false;
        }

        return $this->nanoseconds > $other->nanoseconds;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function ensureNotNegativeInt(int $value, string $which): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException(sprintf(
                'Value for %s must not be negative.',
                $which
            ));
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function ensureNotGreaterThan(int $limit, int $value, string $which): void
    {
        if ($value > $limit) {
            throw new InvalidArgumentException(sprintf(
                'Value for %s must not be greater than %d.',
                $which,
                $limit
            ));
        }
    }
}
