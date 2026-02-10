<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Isla;
use App\Models\Lugar;

class PopulationStatsSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('population_stats')->truncate();
        Lugar::truncate();
        Isla::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $muniPath = base_path('municipios_desde2007_20170101.csv');
        $datasetPath = base_path('dataset-ISTAC_E30243A_000001_1.5_20260130170515.csv');

        $municipios = [];
        if (($handle = fopen($muniPath, 'r')) !== false) {
            fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 7) continue;
                $geocode = trim($row[0], '" ');
                if (str_contains($geocode, '_')) {
                    $geocode = explode('_', $geocode)[0];
                }
                $etiqueta = trim($row[2]);
                $gcd_isla = trim($row[6]);
                if (!empty($geocode) && !empty($etiqueta)) {
                    $municipios[$geocode] = [
                        'nombre' => $etiqueta,
                        'isla_code' => $gcd_isla,
                    ];
                }
            }
            fclose($handle);
        }
        echo "Municipios leídos del CSV: " . count($municipios) . "\n";

        $islaNombres = [];
        if (($handle = fopen($datasetPath, 'r')) !== false) {
            $header = fgetcsv($handle);
            $h = array_flip($header);
            while (($row = fgetcsv($handle)) !== false) {
                $tcode = trim($row[$h['TERRITORIO_CODE']] ?? '');
                $tnombre = trim($row[$h['TERRITORIO#es']] ?? '');
                if (preg_match('/^ES7\d{2}$/', $tcode) && !empty($tnombre)) {
                    $islaNombres[$tcode] = $tnombre;
                }
            }
            fclose($handle);
        }

        $islaMap = [];
        foreach ($islaNombres as $codigo => $nombre) {
            $isla = Isla::create(['nombre' => $nombre, 'codigo' => $codigo]);
            $islaMap[$codigo] = $isla->id;
        }
        echo "Islas creadas: " . count($islaMap) . "\n";

        $lugarMap = [];
        $lugarIsla = [];
        foreach ($municipios as $geocode => $data) {
            $isla_id = $islaMap[$data['isla_code']] ?? null;
            $lugar = Lugar::create([
                'nombre' => $data['nombre'],
                'codigo_lugar' => $geocode,
                'isla_id' => $isla_id,
            ]);
            $lugarMap[$geocode] = $lugar->id;
            $lugarIsla[$geocode] = $isla_id;
        }
        echo "Municipios (lugares) creados: " . count($lugarMap) . "\n";

        $recordCount = 0;
        $batch = [];
        $batchSize = 500;
        $now = now();

        if (($handle = fopen($datasetPath, 'r')) !== false) {
            $header = fgetcsv($handle);
            $h = array_flip($header);

            while (($row = fgetcsv($handle)) !== false) {
                $tcode = trim($row[$h['TERRITORIO_CODE']] ?? '');
                $medida = trim($row[$h['MEDIDAS_CODE']] ?? '');
                $genero = trim($row[$h['SEXO#es']] ?? '');
                $edad = trim($row[$h['EDAD#es']] ?? '');
                $ano = (int) trim($row[$h['TIME_PERIOD_CODE']] ?? '0');
                $poblacion = (int) ($row[$h['OBS_VALUE']] ?? 0);

                if ($medida !== 'POBLACION') continue;
                if (!isset($lugarMap[$tcode])) continue;
                if ($genero !== 'Hombres' && $genero !== 'Mujeres') continue;

                $esEdadIndividual = false;
                if ($edad === '1 año') {
                    $esEdadIndividual = true;
                } elseif ($edad === '100 años o más') {
                    $esEdadIndividual = true;
                } elseif (preg_match('/^(\d+) años$/', $edad, $m)) {
                    $num = (int) $m[1];
                    if ($num >= 0 && $num <= 99) {
                        $esEdadIndividual = true;
                    }
                }
                if (!$esEdadIndividual) continue;

                $batch[] = [
                    'lugar_id' => $lugarMap[$tcode],
                    'isla_id' => $lugarIsla[$tcode],
                    'ano' => $ano,
                    'genero' => $genero,
                    'edad' => $edad,
                    'poblacion' => $poblacion,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $recordCount++;

                if (count($batch) >= $batchSize) {
                    DB::table('population_stats')->insert($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                DB::table('population_stats')->insert($batch);
            }
            fclose($handle);
        }

        echo "\n✅ Seeder completado - Datos 100% desde CSVs:\n";
        echo "- Municipios: " . count($lugarMap) . "\n";
        echo "- Islas: " . count($islaMap) . "\n";
        echo "- Géneros: solo Hombres y Mujeres (sin Total)\n";
        echo "- Edades: 0 a 100 individuales (sin rangos)\n";
        echo "- Años: 2021-2025\n";
        echo "- Registros de población insertados: {$recordCount}\n";
        echo "- Esperados: 88 × 2 × 101 × 5 = 88,880\n";
    }
}
