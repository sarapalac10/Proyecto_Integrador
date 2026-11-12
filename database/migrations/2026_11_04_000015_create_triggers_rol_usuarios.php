<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_estudiantes_bi');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_estudiantes_bi BEFORE INSERT ON estudiantes
FOR EACH ROW
BEGIN
    DECLARE v_rol VARCHAR(20);
    SET v_rol = (SELECT rol FROM users WHERE id = NEW.id_user);
    IF v_rol IS NOT NULL AND v_rol <> 'estudiante' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'RN-17: el usuario enlazado no tiene rol estudiante';
    END IF;
END
SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_estudiantes_bu');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_estudiantes_bu BEFORE UPDATE ON estudiantes
FOR EACH ROW
BEGIN
    DECLARE v_rol VARCHAR(20);
    IF NEW.id_user <> OLD.id_user THEN
        SET v_rol = (SELECT rol FROM users WHERE id = NEW.id_user);
        IF v_rol IS NOT NULL AND v_rol <> 'estudiante' THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'RN-17: el usuario enlazado no tiene rol estudiante';
        END IF;
    END IF;
END
SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_docentes_bi');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_docentes_bi BEFORE INSERT ON docentes
FOR EACH ROW
BEGIN
    DECLARE v_rol VARCHAR(20);
    SET v_rol = (SELECT rol FROM users WHERE id = NEW.id_user);
    IF v_rol IS NOT NULL AND v_rol <> 'docente' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'RN-17: el usuario enlazado no tiene rol docente';
    END IF;
END
SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_docentes_bu');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_docentes_bu BEFORE UPDATE ON docentes
FOR EACH ROW
BEGIN
    DECLARE v_rol VARCHAR(20);
    IF NEW.id_user <> OLD.id_user THEN
        SET v_rol = (SELECT rol FROM users WHERE id = NEW.id_user);
        IF v_rol IS NOT NULL AND v_rol <> 'docente' THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'RN-17: el usuario enlazado no tiene rol docente';
        END IF;
    END IF;
END
SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_users_bu');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_users_bu BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.rol <> OLD.rol THEN
        IF EXISTS (SELECT 1 FROM estudiantes WHERE id_user = OLD.id)
           OR EXISTS (SELECT 1 FROM docentes WHERE id_user = OLD.id) THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'RN-17: no se puede cambiar el rol de un usuario que ya tiene perfil';
        END IF;
    END IF;
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_estudiantes_bi');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_estudiantes_bu');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_docentes_bi');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_docentes_bu');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_users_bu');
    }
};
