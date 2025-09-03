<?php

namespace App\Modules\Equipment\Domain\Enums;

enum EquipmentStatusEnum: string
{
    case WAREHOUSE = 'warehouse'; // На складе

    case ISSUED = 'issued';       // Выдано под отчет

    case INSTALLED = 'installed'; // Установлено

    case SOLD = 'sold';           // Продано

    case RECLAMATION = 'reclamation'; // Рекламация

    case UTIL = 'util';           // Утиль

    case CUSTOMER = 'customer';   // У заказчика
}
