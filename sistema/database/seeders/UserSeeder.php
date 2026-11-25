<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Crea los usuarios de prueba del sistema (contraseña: "password").
     */
    public function run(): void
    {
        // Obtiene los roles como [nombre => id] para asignarlos por su nombre
        $roles = Role::pluck('id', 'name');

        // Lista de usuarios de prueba, uno o más por cada rol
        $users = [
            [
                'role'    => 'Administrador',
                'name'    => 'Juan Pérez',
                'dni'     => '12345678',
                'phone'   => '987654321',
                'tuition' => null,
                'email'   => 'admin@sanjuan.com',
            ],
            [
                'role'    => 'Químico farmacéutico',
                'name'    => 'Rosa Quispe',
                'dni'     => '87654321',
                'phone'   => '912345678',
                'tuition' => 'CQFP-12345',
                'email'   => 'rquispe@sanjuan.com',
            ],
            [
                'role'    => 'Cajero',
                'name'    => 'Carlos Mendoza',
                'dni'     => '45678912',
                'phone'   => '923456789',
                'tuition' => null,
                'email'   => 'cmendoza@sanjuan.com',
            ],
            [
                'role'    => 'Cajero',
                'name'    => 'Lucía Torres',
                'dni'     => '78912345',
                'phone'   => '934567891',
                'tuition' => null,
                'email'   => 'ltorres@sanjuan.com',
            ],
        ];

        foreach ($users as $user) {
            // updateOrCreate busca por correo: si el seeder se ejecuta otra vez no duplica usuarios
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'role_id'  => $roles[$user['role']],
                    'name'     => $user['name'],
                    'dni'      => $user['dni'],
                    'phone'    => $user['phone'],
                    'tuition'  => $user['tuition'],
                    // El modelo User tiene el cast 'hashed': la contraseña se encripta sola
                    'password' => 'password',
                ]
            );
        }
    }
}
