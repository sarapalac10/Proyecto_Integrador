<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_inscripciones_bi');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_inscripciones_bi BEFORE INSERT ON inscripciones
FOR EACH ROW
BEGIN
    DECLARE v_prog_estudiante SMALLINT UNSIGNED;
    DECLARE v_prog_asignatura SMALLINT UNSIGNED;
    DECLARE v_faltantes INT;

    SELECT id_programa INTO v_prog_estudiante
    FROM estudiantes WHERE id_estudiante = NEW.id_estudiante;

    SELECT id_programa INTO v_prog_asignatura
    FROM asignaturas WHERE id_asignatura = NEW.id_asignatura;

    IF v_prog_estudiante <> v_prog_asignatura THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'RN-03: la asignatura no pertenece al programa del estudiante';
    END IF;

    SELECT COUNT(*) INTO v_faltantes
    FROM prerrequisitos p
    WHERE p.id_asignatura = NEW.id_asignatura
      AND NOT EXISTS (
          SELECT 1 FROM inscripciones i
          WHERE i.id_estudiante = NEW.id_estudiante
            AND i.id_asignatura = p.id_asignatura_requisito
            AND i.estado = 'FINALIZADA'
            AND i.nota_final >= 3.0
      );

    IF v_faltantes > 0 THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'RN-04: el estudiante no ha aprobado los prerrequisitos de la asignatura';
    END IF;
END
SQL);

        DB::unprepared('DROP TRIGGER IF EXISTS trg_inscripciones_bu');
        DB::unprepared(<<<'SQL'
CREATE TRIGGER trg_inscripciones_bu BEFORE UPDATE ON inscripciones
FOR EACH ROW
BEGIN
    IF NEW.id_estudiante <> OLD.id_estudiante
       OR NEW.id_asignatura <> OLD.id_asignatura
       OR NEW.id_periodo <> OLD.id_periodo THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'RN-03/04: no se puede cambiar el estudiante, la asignatura ni el periodo de una inscripcion';
    END IF;
END
SQL);
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS trg_inscripciones_bi');
        DB::unprepared('DROP TRIGGER IF EXISTS trg_inscripciones_bu');
    }
};
