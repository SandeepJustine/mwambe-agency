<?php

declare(strict_types=1);

namespace Mwambe\Core;

use Closure;

final class Container
{
    /** @var array<string, mixed> */
    private array $bindings = [];

    public function set(string $id, mixed $value): void
    {
        $this->bindings[$id] = $value;
    }

    public function bind(string $id, Closure $factory): void
    {
        $this->bindings[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        if (!isset($this->bindings[$id])) {
            if (class_exists($id)) {
                return $this->build($id);
            }
            throw new \RuntimeException("Nothing bound for {$id}");
        }
        $binding = $this->bindings[$id];
        if ($binding instanceof Closure) {
            $this->bindings[$id] = $binding($this);
        }
        return $this->bindings[$id];
    }

    private function build(string $class): object
    {
        $reflection = new \ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return new $class();
        }
        $args = [];
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $args[] = $this->get($type->getName());
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException("Cannot autowire {$class}: unresolved \${$param->getName()}");
            }
        }
        return $reflection->newInstanceArgs($args);
    }

    public function getWithArgs(string $id, array $args = []): object
    {
        if (!class_exists($id)) {
            throw new \RuntimeException("Cannot instantiate {$id}");
        }
        $reflection = new \ReflectionClass($id);
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstance();
        }
        $resolved = [];
        $argIndex = 0;
        foreach ($constructor->getParameters() as $param) {
            $type = $param->getType();
            if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                $resolved[] = $this->get($type->getName());
            } elseif (array_key_exists($argIndex, $args)) {
                $resolved[] = $args[$argIndex];
                $argIndex++;
            } elseif ($param->isDefaultValueAvailable()) {
                $resolved[] = $param->getDefaultValue();
            } else {
                throw new \RuntimeException("Cannot resolve parameter \${$param->getName()} for {$id}");
            }
        }
        return $reflection->newInstanceArgs($resolved);
    }
}