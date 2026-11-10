<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_solicitudes_bi');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_solicitudes_bi BEFORE INSERT ON solicitudes
FOR EACH ROW
BEGIN
    DECLARE v_estado VARCHAR(20);
    DECLARE v_limite DATE;
    DECLARE v_activas INT;

    SELECT i.estado, p.fecha_limite_cancelacion INTO v_estado, v_limite
    FROM inscripciones i
    JOIN periodos_academicos p ON p.id_periodo = i.id_periodo
    WHERE i.id_inscripcion = NEW.id_inscripcion;

    IF v_estado <> 'CURSANDO' THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'RN-06: solo se puede solicitar la cancelacion de una inscripcion en estado CURSANDO';
    END IF;

    IF CURDATE() > v_limite THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'RN-07: la fecha limite de cancelacion del periodo ya vencio';
    END IF;

    SELECT COUNT(*) INTO v_activas
    FROM solicitudes s
    WHERE s.id_inscripcion = NEW.id_inscripcion
      AND COALESCE((
            SELECT sg.estado
            FROM seguimiento_solicitud sg
            WHERE sg.id_solicitud = s.id_solicitud
            ORDER BY sg.id_seguimiento DESC
            LIMIT 1
          ), 'RADICADA') IN ('RADICADA', 'EN_REVISION');

    IF v_activas > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'RN-08: la inscripcion ya tiene una solicitud activa';
    END IF;
END
SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_solicitudes_bu');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_solicitudes_bu BEFORE UPDATE ON solicitudes
FOR EACH ROW
BEGIN
    IF NEW.id_inscripcion <> OLD.id_inscripcion THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'RN-08: no se puede cambiar la inscripcion de una solicitud';
    END IF;
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_solicitudes_bi');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_solicitudes_bu');
    }
};
