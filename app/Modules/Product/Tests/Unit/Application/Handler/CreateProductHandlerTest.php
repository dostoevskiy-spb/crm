<?php

declare(strict_types=1);

namespace App\Modules\Product\Tests\Unit\Application\Handler;

use App\Modules\Product\Application\Command\CreateProductCommand;
use App\Modules\Product\Application\DTO\CreateProductDTO;
use App\Modules\Product\Application\Handler\CreateProductHandler;
use App\Modules\Product\Domain\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Domain\Models\Product;
use PHPUnit\Framework\TestCase;

final class CreateProductHandlerTest extends TestCase
{
    public function test_creates_product_and_returns_uid(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('existsBySku')
            ->willReturn(false);
        $repo->expects($this->once())
            ->method('existsByCode1c')
            ->willReturn(false);
        $repo->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn (Product $p) => $p);

        $handler = new CreateProductHandler($repo);

        $dto = new CreateProductDTO(
            name: 'GPS',
            status: 'active',
            type: 'item',
            unit: 'шт.',
            sku: 'APP-001',
            groupName: 'Equip',
            subgroupName: 'Trackers',
            code1c: 'C1',
            salePrice: '100.00'
        );

        $uid = $handler(new CreateProductCommand($dto));
        $this->assertIsString($uid);
        $this->assertNotEmpty($uid);
        $this->assertMatchesRegularExpression('/^[0-9a-fA-F-]{36}$/', $uid);
    }

    public function test_throws_when_sku_exists(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('existsBySku')
            ->willReturn(true);
        $repo->expects($this->never())
            ->method('save');

        $handler = new CreateProductHandler($repo);

        $dto = new CreateProductDTO(
            name: 'GPS',
            status: 'active',
            type: 'item',
            unit: 'шт.',
            sku: 'DUP-001'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product with this SKU already exists');

        $handler(new CreateProductCommand($dto));
    }

    public function test_throws_when_code1c_exists(): void
    {
        $repo = $this->createMock(ProductRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('existsBySku')
            ->willReturn(false);
        $repo->expects($this->once())
            ->method('existsByCode1c')
            ->with('C1')
            ->willReturn(true);
        $repo->expects($this->never())
            ->method('save');

        $handler = new CreateProductHandler($repo);

        $dto = new CreateProductDTO(
            name: 'GPS',
            status: 'active',
            type: 'item',
            unit: 'шт.',
            sku: 'APP-002',
            code1c: 'C1'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product with this 1C code already exists');

        $handler(new CreateProductCommand($dto));
    }
}
