<?php

namespace App\Support;

use RuntimeException;

class LabFixtures
{
    public static function sql(string $name): string
    {
        return match ($name) {
            'conference' => self::conference(),
            default => throw new RuntimeException('Неизвестная фикстура: '.$name),
        };
    }

    private static function conference(): string
    {
        return <<<'SQL'
CREATE TABLE users (
    id INTEGER PRIMARY KEY,
    fio TEXT NOT NULL,
    login TEXT NOT NULL
);

CREATE TABLE rooms (
    id INTEGER PRIMARY KEY,
    name TEXT NOT NULL,
    capacity INTEGER NOT NULL
);

CREATE TABLE applications (
    id INTEGER PRIMARY KEY,
    user_id INTEGER NOT NULL,
    room_id INTEGER NOT NULL,
    start_date TEXT NOT NULL,
    payment_type TEXT NOT NULL,
    status TEXT NOT NULL,
    rating INTEGER NULL,
    review TEXT NULL
);

INSERT INTO users (id, fio, login) VALUES
    (1, 'Иванова Мария Сергеевна', 'maria2027'),
    (2, 'Петров Пётр Петрович', 'petrov2027'),
    (3, 'Администратор Портала', 'admin');

INSERT INTO rooms (id, name, capacity) VALUES
    (1, 'Большая конференц-аудитория', 120),
    (2, 'Коворкинг-зона «Вектор»', 40),
    (3, 'Кинозал «Пленум»', 90);

INSERT INTO applications (id, user_id, room_id, start_date, payment_type, status, rating, review) VALUES
    (1, 1, 2, '2026-11-12', 'sbp', 'new', NULL, NULL),
    (2, 1, 3, '2026-10-01', 'in_person', 'completed', 5, 'Всё понравилось'),
    (3, 2, 1, '2026-12-05', 'in_person', 'assigned', NULL, NULL),
    (4, 2, 2, '2026-11-20', 'sbp', 'new', NULL, NULL),
    (5, 1, 1, '2026-09-15', 'in_person', 'new', NULL, NULL);
SQL;
    }
}
