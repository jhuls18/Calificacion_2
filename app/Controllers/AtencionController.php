<?php
declare(strict_types=1);

final class AtencionController
{
    public static function index(): void
    {
        $u = require_login();
        view('atencion', [
            'title' => 'Herramientas de Atención Vuela',
            'user' => $u,
        ]);
    }
}
