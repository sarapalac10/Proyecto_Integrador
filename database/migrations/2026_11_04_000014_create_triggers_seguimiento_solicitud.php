<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_seguimiento_bi');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_seguimiento_bi BEFORE INSERT ON seguimiento_solicitud
FOR EACH ROW
BEGIN
    DECLARE v_anterior VARCHAR(20);
    DECLARE v_msg VARCHAR(128);

    SET v_anterior = (
        SELECT estado
        FROM seguimiento_solicitud
        WHERE id_solicitud = NEW.id_solicitud
        ORDER BY id_seguimiento DESC
        LIMIT 1
    );

    IF v_anterior IS NULL THEN
        IF NEW.estado <> 'RADICADA' THEN
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'RN-09: el primer estado de una solicitud debe ser RADICADA';
        END IF;
    ELSEIF NOT (
        (v_anterior = 'RADICADA' AND NEW.estado IN ('EN_REVISION', 'DESISTIDA'))
        OR (v_anterior = 'EN_REVISION' AND NEW.estado IN ('APROBADA', 'RECHAZADA', 'DESISTIDA'))
    ) THEN
        SET v_msg = CONCAT('RN-09: transicion no permitida de ', v_anterior, ' a ', NEW.estado);
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = v_msg;
    END IF;
END
SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_seguimiento_bu');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_seguimiento_bu BEFORE UPDATE ON seguimiento_solicitud
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'RN-11: el seguimiento de solicitudes no admite modificaciones';
END
SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_seguimiento_bd');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_seguimiento_bd BEFORE DELETE ON seguimiento_solicitud
FOR EACH ROW
BEGIN
    SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'RN-11: el seguimiento de solicitudes no admite eliminaciones';
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_seguimiento_bi');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_seguimiento_bu');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_seguimiento_bd');
    }
};
