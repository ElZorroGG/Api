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
        $dataset = base_path('dataset-ISTAC_E30243A_000001_1.5_20260130170515.csv');
        $muniFile = base_path('municipios_desde2007_20170101.csv');

        $islandNames = [];
        if (($h = fopen($dataset, 'r')) !== false) {
            $header = fgetcsv($h);
            $idx = array_flip($header ?: []);
            while (($row = fgetcsv($h)) !== false) {
                if (!isset($row[$idx['TERRITORIO_CODE']])) continue;
                $code = $row[$idx['TERRITORIO_CODE']];
                $measure = $row[$idx['MEDIDAS_CODE']] ?? null;
                $territorio = $row[$idx['TERRITORIO#es']] ?? null;
                if (is_string($code) && str_starts_with($code, 'ES') && ($measure === 'POBLACION')) {
                    if (!isset($islandNames[$code])) {
                        $islandNames[$code] = $territorio;
                    }
                }
            }
            fclose($h);
        }

        $muniMap = [];
        if (($h = fopen($muniFile, 'r')) !== false) {
            $header = fgetcsv($h);
            $idx = array_flip($header ?: []);
            while (($row = fgetcsv($h)) !== false) {
                $geocode = trim(trim($row[$idx['geocode']] ?? ''), '"');
                $gcd_isla = $row[$idx['gcd_isla']] ?? null;
                $etiqueta = $row[$idx['etiqueta']] ?? null;
                if ($geocode !== '') {
                    $muniMap[$geocode] = ['island_code' => $gcd_isla, 'etiqueta' => $etiqueta];
                }
            }
            fclose($h);
        }

        foreach ($islandNames as $codigo => $nombre) {
            Isla::updateOrCreate(['codigo' => $codigo], ['nombre' => $nombre]);
        }

        if (($h = fopen($muniFile, 'r')) !== false) {
            $header = fgetcsv($h);
            $idx = array_flip($header ?: []);
            while (($row = fgetcsv($h)) !== false) {
                $geocode = trim(trim($row[$idx['geocode']] ?? ''), '"');
                $gcd_isla = $row[$idx['gcd_isla']] ?? null;
                $etiqueta = $row[$idx['etiqueta']] ?? null;
                if ($geocode === '') continue;
                $isla_id = null;
                if ($gcd_isla && isset($islandNames[$gcd_isla])) {
                    $isla = Isla::where('codigo', $gcd_isla)->first();
                    $isla_id = $isla->id ?? null;
                }
                Lugar::updateOrCreate(['codigo_lugar' => $geocode], ['nombre' => $etiqueta, 'isla_id' => $isla_id]);
            }
            fclose($h);
        }

        if (($h = fopen($dataset, 'r')) !== false) {
            $header = fgetcsv($h);
            $idx = array_flip($header ?: []);
            while (($row = fgetcsv($h)) !== false) {
                $measure = $row[$idx['MEDIDAS_CODE']] ?? null;
                if ($measure !== 'POBLACION') continue;
                $territorio = $row[$idx['TERRITORIO#es']] ?? null;
                $territorio_code = $row[$idx['TERRITORIO_CODE']] ?? null;
                $ano = (int)($row[$idx['TIME_PERIOD_CODE']] ?? 0);
                $genero = $row[$idx['SEXO#es']] ?? null;
                $edad = $row[$idx['EDAD#es']] ?? null;
                $value = $row[$idx['OBS_VALUE']] ?? null;
                if ($value === '' || $value === null) continue;
                $genero_trim = is_string($genero) ? trim($genero) : '';
                $edad_trim = is_string($edad) ? trim($edad) : '';
                if (strcasecmp($genero_trim, 'Total') === 0 || strcasecmp($edad_trim, 'Total') === 0) continue;
                $poblacion = is_numeric($value) ? (int)$value : null;
                if ($poblacion === 0) continue;
                $lugar_id = null;
                $isla_id = null;
                if (is_string($territorio_code) && str_starts_with($territorio_code, 'ES')) {
                    $isla = Isla::where('codigo', $territorio_code)->first();
                    $isla_id = $isla->id ?? null;
                } else {
                    $lugar = Lugar::where('codigo_lugar', $territorio_code)->first();
                    if ($lugar) {
                        $lugar_id = $lugar->id;
                        $isla_id = $lugar->isla_id;
                    } else {
                        if (isset($muniMap[$territorio_code])) {
                            $gcd_isla = $muniMap[$territorio_code]['island_code'] ?? null;
                            $isla = Isla::where('codigo', $gcd_isla)->first();
                            $isla_id = $isla->id ?? null;
                        }
                        $nuevo = Lugar::updateOrCreate(['codigo_lugar' => $territorio_code], ['nombre' => $territorio, 'isla_id' => $isla_id]);
                        $lugar_id = $nuevo->id;
                    }
                }
                DB::table('population_stats')->updateOrInsert([
                    'lugar_id' => $lugar_id,
                    'isla_id' => $isla_id,
                    'ano' => $ano,
                    'genero' => $genero,
                    'edad' => $edad,
                ], ['poblacion' => $poblacion, 'updated_at' => now(), 'created_at' => now()]);
            }
            fclose($h);
        }
    }
}
