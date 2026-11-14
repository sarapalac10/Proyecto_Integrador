<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tablas auditadas: clave primaria y columnas que se registran.
     * created_at y updated_at no se registran: son ruido tecnico.
     */
    private function tablas(): array
    {
        return [
            'users' => ['pk' => 'id', 'cols' => ['id', 'rol', 'email', 'activo']],
            'estudiantes' => ['pk' => 'id_estudiante', 'cols' => ['id_estudiante', 'id_user', 'id_programa', 'codigo_estudiantil', 'nombres', 'apellidos', 'tipo_documento', 'documento']],
            'docentes' => ['pk' => 'id_docente', 'cols' => ['id_docente', 'id_user', 'nombres', 'apellidos', 'tipo_documento', 'documento']],
            'perfil_sociodemografico' => ['pk' => 'id_estudiante', 'cols' => ['id_estudiante', 'fecha_nacimiento', 'sexo', 'estrato', 'municipio_residencia', 'trabaja']],
            'periodos_academicos' => ['pk' => 'id_periodo', 'cols' => ['id_periodo', 'codigo', 'fecha_inicio', 'fecha_fin', 'fecha_limite_cancelacion']],
            'inscripciones' => ['pk' => 'id_inscripcion', 'cols' => ['id_inscripcion', 'id_estudiante', 'id_asignatura', 'id_periodo', 'estado', 'nota_final']],
            'solicitudes' => ['pk' => 'id_solicitud', 'cols' => ['id_solicitud', 'id_inscripcion', 'motivo', 'justificacion', 'fecha_radicacion']],
        ];
    }

    private function objeto(string $ref, array $cols, array $extra = []): string
    {
        $pares = array_map(fn ($c) => "'{$c}', {$ref}.{$c}", $cols);

        return 'JSON_OBJECT(' . implode(', ', array_merge($pares, $extra)) . ')';
    }

    public function up(): void
    {
        $insert = 'INSERT INTO auditoria_cambios '
            . '(tabla, id_registro, operacion, valores_anteriores, valores_nuevos, id_user)';

        foreach ($this->tablas() as $tabla => $def) {
            $pk = $def['pk'];
            $cols = $def['cols'];
            $esUsers = $tabla === 'users';
            $pwdCambio = '(NOT (OLD.password <=> NEW.password))';

            $viejo = $this->objeto('OLD', $cols);
            $nuevo = $this->objeto('NEW', $cols);
            $nuevoUpd = $this->objeto('NEW', $cols, $esUsers ? ["'password_modificada', {$pwdCambio}"] : []);
            $cambio = "NOT ({$viejo} <=> {$nuevo})" . ($esUsers ? " OR {$pwdCambio}" : '');

            DB::unprepared("DROP TRIGGER IF EXISTS trg_aud_{$tabla}_ai");
            DB::unprepared(
                "CREATE TRIGGER trg_aud_{$tabla}_ai AFTER INSERT ON {$tabla} FOR EACH ROW "
                . "{$insert} VALUES ('{$tabla}', CAST(NEW.{$pk} AS CHAR), 'INSERT', NULL, {$nuevo}, @app_user_id)"
            );

            DB::unprepared("DROP TRIGGER IF EXISTS trg_aud_{$tabla}_au");
            DB::unprepared(
                "CREATE TRIGGER trg_aud_{$tabla}_au AFTER UPDATE ON {$tabla} FOR EACH ROW BEGIN "
                . "IF {$cambio} THEN "
                . "{$insert} VALUES ('{$tabla}', CAST(NEW.{$pk} AS CHAR), 'UPDATE', {$viejo}, {$nuevoUpd}, @app_user_id); "
                . 'END IF; END'
            );

            DB::unprepared("DROP TRIGGER IF EXISTS trg_aud_{$tabla}_ad");
            DB::unprepared(
                "CREATE TRIGGER trg_aud_{$tabla}_ad AFTER DELETE ON {$tabla} FOR EACH ROW "
                . "{$insert} VALUES ('{$tabla}', CAST(OLD.{$pk} AS CHAR), 'DELETE', {$viejo}, NULL, @app_user_id)"
            );
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_auditoria_bu');
        DB::unprepared(
            "CREATE TRIGGER trg_auditoria_bu BEFORE UPDATE ON auditoria_cambios FOR EACH ROW "
            . "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RN-11: la auditoria no admite modificaciones'"
        );

        DB::unprepared('DROP TRIGGER IF EXISTS trg_auditoria_bd');
        DB::unprepared(
            "CREATE TRIGGER trg_auditoria_bd BEFORE DELETE ON auditoria_cambios FOR EACH ROW "
            . "SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'RN-11: la auditoria no admite eliminaciones'"
        );
    }

    public function down(): void
    {
        foreach (array_keys($this->tablas()) as $tabla) {
            foreach (['ai', 'au', 'ad'] as $sufijo) {
                DB::unprepared("DROP TRIGGER IF EXISTS trg_aud_{$tabla}_{$sufijo}");
            }
        }

        DB::unprepared('DROP TRIGGER IF EXISTS trg_auditoria_bu');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_auditoria_bd');
    }
};
