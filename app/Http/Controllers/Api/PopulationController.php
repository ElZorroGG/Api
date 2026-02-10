<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Isla;
use App\Models\Lugar;
use App\Models\PopulationStat;
use Illuminate\Http\Request;

class PopulationController extends Controller
{
    public function getMunicipalityPopulation(Request $request)
    {
        $query = PopulationStat::query()
            ->with(['lugar' => fn($q) => $q->select('id', 'nombre', 'isla_id')])
            ->whereNotNull('lugar_id');

        // Filtro por nombre de municipio
        if ($request->has('municipio')) {
            $nombreMunicipio = $request->get('municipio');
            $lugarIds = Lugar::where('nombre', 'LIKE', "%{$nombreMunicipio}%")->pluck('id');
            $query->whereIn('lugar_id', $lugarIds);
        }

        // Aplicar filtros
        $this->applyFilters($query, $request);

        $allData = $query->get();
        $maxAno = $allData->max('ano');

        $data = $allData->groupBy('lugar_id')
            ->map(function ($group) use ($maxAno) {
                $lugar = $group->first()->lugar;
                $poblacionUltimoAno = (int) $group->where('ano', $maxAno)->sum('poblacion');

                return [
                    'lugar_id' => $group->first()->lugar_id,
                    'municipio' => $lugar->nombre ?? 'N/A',
                    'isla_id' => $lugar->isla_id,
                    'total_poblacion' => $poblacionUltimoAno,
                    'ultimo_ano' => $maxAno,
                ];
            });

        // Ordenar alfabéticamente por defecto
        $orderBy = $request->get('order_by', 'municipio');
        $order = $request->get('order', 'asc');

        if ($orderBy === 'municipio') {
            $data = ($order === 'desc')
                ? $data->sortByDesc(fn($item) => $item['municipio'])
                : $data->sortBy(fn($item) => $item['municipio']);
        } elseif ($orderBy === 'total_poblacion') {
            $data = ($order === 'desc')
                ? $data->sortByDesc(fn($item) => $item['total_poblacion'])
                : $data->sortBy(fn($item) => $item['total_poblacion']);
        }

        $response = [
            'success' => true,
            'data' => $data->values(),
            'ultimo_ano' => $maxAno,
            'total' => (int) $data->sum('total_poblacion'),
            'filtros_aplicados' => [],
        ];

        // Mostrar filtro de municipio si se aplicó
        if ($request->has('municipio')) {
            $response['filtros_aplicados']['municipio'] = $request->get('municipio');
        }

        // Mostrar filtro de género si se aplicó
        if ($request->has('genero')) {
            $response['filtros_aplicados']['genero'] = $request->get('genero');
        }

        // Mostrar filtro de año si se aplicó
        if ($request->has('ano')) {
            $response['filtros_aplicados']['ano'] = $request->get('ano');
        }

        // Mostrar rangos de edad aplicados si se filtró por rango
        if ($request->has('edad_min') && $request->has('edad_max')) {
            $edadMin = (int) $request->get('edad_min');
            $edadMax = (int) $request->get('edad_max');

            $edades = PopulationStat::distinct('edad')
                ->whereNotNull('edad')
                ->pluck('edad')
                ->toArray();

            $edadesAplicadas = array_values(array_filter($edades, function ($edad) use ($edadMin, $edadMax) {
                // Formato rango: "De X a Y años"
                if (preg_match('/De\s+(\d+)\s+a\s+(\d+)/', $edad, $matches)) {
                    $rangeMin = (int) $matches[1];
                    $rangeMax = (int) $matches[2];
                    return !($rangeMax < $edadMin || $rangeMin > $edadMax);
                }
                // Formato abierto: "X años o más"
                if (preg_match('/(\d+)\s+años\s+o\s+más/', $edad, $matches)) {
                    $val = (int) $matches[1];
                    return $val <= $edadMax;
                }
                // Formato individual: "X años" o "X año"
                if (preg_match('/^(\d+)\s+años?$/', $edad, $matches)) {
                    $val = (int) $matches[1];
                    return $val >= $edadMin && $val <= $edadMax;
                }
                return false;
            }));

            sort($edadesAplicadas);
            $response['filtros_aplicados']['rango_edad_solicitado'] = "De {$edadMin} a {$edadMax} años";
            $response['filtros_aplicados']['rangos_edad_aplicados'] = $edadesAplicadas;
        }

        // Si no hay filtros, quitar el campo
        if (empty($response['filtros_aplicados'])) {
            unset($response['filtros_aplicados']);
        }

        return response()->json($response);
    }

    public function getIslandPopulation(Request $request)
    {
        $byMunicipality = $request->get('breakdown') === 'true';

        if ($byMunicipality) {
            return $this->getIslandPopulationByMunicipality($request);
        }

        $query = PopulationStat::query()
            ->with(['isla' => fn($q) => $q->select('id', 'nombre')])
            ->whereNotNull('isla_id');

        // Filtro por nombre de isla
        if ($request->has('isla')) {
            $nombreIsla = $request->get('isla');
            $islaIds = Isla::where('nombre', 'LIKE', "%{$nombreIsla}%")->pluck('id');
            $query->whereIn('isla_id', $islaIds);
        }

        $this->applyFilters($query, $request);

        $allData = $query->get();
        $maxAno = $allData->max('ano');

        $data = $allData->groupBy('isla_id')
            ->map(function ($group) use ($maxAno) {
                $isla = $group->first()->isla;
                $poblacionUltimoAno = (int) $group->where('ano', $maxAno)->sum('poblacion');

                return [
                    'isla_id' => $group->first()->isla_id,
                    'isla' => $isla->nombre ?? 'N/A',
                    'total_poblacion' => $poblacionUltimoAno,
                    'ultimo_ano' => $maxAno,
                ];
            });

        // Ordenar alfabéticamente por defecto
        $orderBy = $request->get('order_by', 'isla');
        $order = $request->get('order', 'asc');

        if ($orderBy === 'isla') {
            $data = ($order === 'desc')
                ? $data->sortByDesc(fn($item) => $item['isla'])
                : $data->sortBy(fn($item) => $item['isla']);
        } elseif ($orderBy === 'total_poblacion') {
            $data = ($order === 'desc')
                ? $data->sortByDesc(fn($item) => $item['total_poblacion'])
                : $data->sortBy(fn($item) => $item['total_poblacion']);
        }

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'ultimo_ano' => $maxAno,
            'total' => (int) $data->sum('total_poblacion'),
        ]);
    }

    /**
     * Obtener población de isla desglosada por municipios
     */
    private function getIslandPopulationByMunicipality(Request $request)
    {
        $query = PopulationStat::query()
            ->with(['lugar' => fn($q) => $q->select('id', 'nombre', 'isla_id'), 'isla' => fn($q) => $q->select('id', 'nombre')])
            ->whereNotNull('lugar_id');

        // Filtro por nombre de isla
        if ($request->has('isla')) {
            $nombreIsla = $request->get('isla');
            $islaIds = Isla::where('nombre', 'LIKE', "%{$nombreIsla}%")->pluck('id');
            $query->whereIn('isla_id', $islaIds);
        }

        // Filtro por nombre de municipio
        if ($request->has('municipio')) {
            $nombreMunicipio = $request->get('municipio');
            $lugarIds = Lugar::where('nombre', 'LIKE', "%{$nombreMunicipio}%")->pluck('id');
            $query->whereIn('lugar_id', $lugarIds);
        }

        $this->applyFilters($query, $request);

        $allData = $query->get();
        $maxAno = $allData->max('ano');

        $data = $allData->groupBy(fn($item) => $item->isla->nombre ?? 'N/A')
            ->map(function($municipios, $islandName) use ($maxAno) {
                return [
                    'isla' => $islandName,
                    'municipios' => $municipios->groupBy('lugar_id')
                        ->map(function ($group) use ($maxAno) {
                            $poblacionUltimoAno = (int) $group->where('ano', $maxAno)->sum('poblacion');
                            return [
                                'municipio' => $group->first()->lugar->nombre ?? 'N/A',
                                'total_poblacion' => $poblacionUltimoAno,
                                'ultimo_ano' => $maxAno,
                            ];
                        })
                        ->values(),
                ];
            });

        // Ordenar municipios dentro de cada isla
        $orderBy = $request->get('order_by', 'municipio');
        $order = $request->get('order', 'asc');
        $sortField = ($orderBy === 'total_poblacion') ? 'total_poblacion' : 'municipio';

        $data = $data->map(function($island) use ($sortField, $order) {
            $island['municipios'] = collect($island['municipios'])
                ->sortBy($sortField, SORT_REGULAR, $order === 'desc')
                ->values()
                ->toArray();
            return $island;
        });

        $latestYearTotal = (int) $allData->where('ano', $maxAno)->sum('poblacion');

        return response()->json([
            'success' => true,
            'data' => $data->sortBy('isla')->values(),
            'ultimo_ano' => $maxAno,
            'total' => $latestYearTotal,
        ]);
    }

    public function getPopulationEvolution(Request $request)
    {
        $type = $request->get('type', 'municipality'); // municipality o island
        $entityId = $request->get('id'); // lugar_id o isla_id
        $nombre = $request->get('nombre'); // búsqueda por nombre
        $breakdown = $request->get('breakdown') === 'true'; // Desglose por municipio si type=island

        // Buscar por nombre si no se proporciona ID
        if (!$entityId && $nombre) {
            if ($type === 'island') {
                $isla = Isla::where('nombre', 'LIKE', "%$nombre%")->first();
                if (!$isla) {
                    return response()->json(['error' => "No se encontró isla con nombre '$nombre'"], 404);
                }
                $entityId = $isla->id;
            } else {
                $lugar = Lugar::where('nombre', 'LIKE', "%$nombre%")->first();
                if (!$lugar) {
                    return response()->json(['error' => "No se encontró municipio con nombre '$nombre'"], 404);
                }
                $entityId = $lugar->id;
            }
        }

        if (!$entityId) {
            return response()->json(['error' => 'Se requiere id o nombre'], 400);
        }

        // Validar que la entidad existe
        if ($type === 'island') {
            $isla = Isla::find($entityId);
            if (!$isla) {
                return response()->json(['error' => 'Island not found'], 404);
            }
            $entityName = $isla->nombre;
        } else {
            $lugar = Lugar::find($entityId);
            if (!$lugar) {
                return response()->json(['error' => 'Municipality not found'], 404);
            }
            $entityName = $lugar->nombre;
        }

        // Si es isla Y pide desglose por municipio
        if ($type === 'island' && $breakdown) {
            return $this->getIslandEvolutionByMunicipality($entityId, $request);
        }

        $groupBy = ($type === 'island') ? 'isla_id' : 'lugar_id';
        $query = PopulationStat::query()
            ->where($groupBy, $entityId)
            ->orderBy('ano');

        $this->applyFilters($query, $request);

        $data = $query->groupBy('ano')
            ->selectRaw('ano, SUM(poblacion) as poblacion')
            ->get();

        if ($data->isEmpty()) {
            return response()->json(['error' => 'No data found'], 404);
        }

        $firstPopulation = $data->first()->poblacion;
        $evolution = $data->map(function($item) use ($firstPopulation) {
            return [
                'ano' => $item->ano,
                'poblacion' => (int) $item->poblacion,
                'porcentaje_cambio' => round((($item->poblacion - $firstPopulation) / $firstPopulation) * 100, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'entity_type' => $type,
            'entity_id' => $entityId,
            'entity_name' => $entityName,
            'data' => $evolution,
            'total_inicial' => (int) $firstPopulation,
            'total_final' => (int) $data->last()->poblacion,
        ]);
    }

    /**
     * Obtener evolución de una isla desglosada por municipios
     */
    private function getIslandEvolutionByMunicipality($islaId, Request $request)
    {
        $isla = Isla::find($islaId);
        
        $query = PopulationStat::query()
            ->where('isla_id', $islaId)
            ->whereNotNull('lugar_id')
            ->with('lugar')
            ->orderBy('ano');

        $this->applyFilters($query, $request);

        $allData = $query->get();

        if ($allData->isEmpty()) {
            return response()->json(['error' => 'No data found'], 404);
        }

        // Agrupar por municipio y año
        $evolutionByMunicipality = $allData->groupBy(fn($item) => $item->lugar->nombre)
            ->map(function($municipilityData, $municipilityName) {
                $byYear = $municipilityData->groupBy('ano')
                    ->map(fn($group) => [
                        'ano' => $group->first()->ano,
                        'poblacion' => (int) $group->sum('poblacion'),
                    ])
                    ->sortBy('ano')
                    ->values();

                $firstPopulation = $byYear->first()['poblacion'];
                $evolution = $byYear->map(function($item) use ($firstPopulation) {
                    return [
                        'ano' => $item['ano'],
                        'poblacion' => $item['poblacion'],
                        'porcentaje_cambio' => round((($item['poblacion'] - $firstPopulation) / $firstPopulation) * 100, 2),
                    ];
                });

                return [
                    'municipio' => $municipilityName,
                    'total_inicial' => $firstPopulation,
                    'total_final' => $evolution->last()['poblacion'],
                    'datos' => $evolution,
                ];
            })
            ->values();

        // Ordenar municipios según parámetros
        $orderBy = $request->get('order_by', 'municipio');
        $order = $request->get('order', 'asc');
        $sortField = ($orderBy === 'total_poblacion') ? 'total_final' : 'municipio';

        $evolutionByMunicipality = $evolutionByMunicipality
            ->sortBy($sortField, SORT_REGULAR, $order === 'desc')
            ->values();

        return response()->json([
            'success' => true,
            'entity_type' => 'island',
            'entity_id' => $islaId,
            'entity_name' => $isla->nombre,
            'breakdown' => 'by_municipality',
            'data' => $evolutionByMunicipality,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, municipalities, islands

        if (strlen($query) < 2) {
            return response()->json(['error' => 'Query must be at least 2 characters'], 400);
        }

        $results = [];

        if ($type === 'all' || $type === 'municipalities') {
            $municipios = Lugar::where('nombre', 'LIKE', "%$query%")
                ->with('isla')
                ->orderBy('nombre')
                ->get()
                ->map(fn($lugar) => [
                    'id' => $lugar->id,
                    'nombre' => $lugar->nombre,
                    'tipo' => 'municipio',
                    'isla' => $lugar->isla?->nombre,
                ]);
            $results = array_merge($results, $municipios->toArray());
        }

        if ($type === 'all' || $type === 'islands') {
            $islas = Isla::where('nombre', 'LIKE', "%$query%")
                ->orderBy('nombre')
                ->get()
                ->map(fn($isla) => [
                    'id' => $isla->id,
                    'nombre' => $isla->nombre,
                    'tipo' => 'isla',
                ]);
            $results = array_merge($results, $islas->toArray());
        }

        // Ordenar alfabéticamente
        usort($results, fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));

        return response()->json([
            'success' => true,
            'query' => $query,
            'data' => array_values($results),
        ]);
    }

    public function getFilters(Request $request)
    {
        return response()->json([
            'success' => true,
            'filters' => [
                'genero' => PopulationStat::distinct('genero')
                    ->whereNotNull('genero')
                    ->orderBy('genero')
                    ->pluck('genero')
                    ->values(),
                'edad' => PopulationStat::distinct('edad')
                    ->whereNotNull('edad')
                    ->orderBy('edad')
                    ->pluck('edad')
                    ->values(),
                'anos' => PopulationStat::distinct('ano')
                    ->orderBy('ano')
                    ->pluck('ano')
                    ->values(),
            ],
        ]);
    }

    /**
     * Aplicar filtros a una query
     */
    private function applyFilters(&$query, Request $request)
    {
        // Filtro por género
        if ($request->has('genero')) {
            $query->where('genero', $request->get('genero'));
        }

        // Filtro por edad
        if ($request->has('edad')) {
            $query->where('edad', $request->get('edad'));
        }

        // Filtro por rango de edad
        if ($request->has('edad_min') && $request->has('edad_max')) {
            $edadMin = (int)$request->get('edad_min');
            $edadMax = (int)$request->get('edad_max');
            
            $edades = PopulationStat::distinct('edad')
                ->whereNotNull('edad')
                ->pluck('edad')
                ->toArray();
            
            $edadesValidas = array_filter($edades, function($edad) use ($edadMin, $edadMax) {
                // Formato rango: "De X a Y años"
                if (preg_match('/De\s+(\d+)\s+a\s+(\d+)/', $edad, $matches)) {
                    $rangeMin = (int)$matches[1];
                    $rangeMax = (int)$matches[2];
                    return !($rangeMax < $edadMin || $rangeMin > $edadMax);
                }
                // Formato abierto: "X años o más"
                if (preg_match('/(\d+)\s+años\s+o\s+más/', $edad, $matches)) {
                    $val = (int)$matches[1];
                    return $val <= $edadMax;
                }
                // Formato individual: "X años" o "X año"
                if (preg_match('/^(\d+)\s+años?$/', $edad, $matches)) {
                    $val = (int)$matches[1];
                    return $val >= $edadMin && $val <= $edadMax;
                }
                return false;
            });
            
            if (!empty($edadesValidas)) {
                $query->whereIn('edad', $edadesValidas);
            }
        }

        // Filtro por año
        if ($request->has('ano')) {
            $ano = $request->get('ano');
            if (is_array($ano)) {
                $query->whereIn('ano', $ano);
            } else {
                $query->where('ano', $ano);
            }
        }

        // Filtro por rango de años
        if ($request->has('ano_desde') && $request->has('ano_hasta')) {
            $query->whereBetween('ano', [
                $request->get('ano_desde'),
                $request->get('ano_hasta')
            ]);
        }
    }

    // =========================================================================
    // Swagger / OpenAPI Annotations
    // =========================================================================

    /**
     * @OA\Get(
     *      path="/api/population/municipalities",
     *      operationId="getMunicipalityPopulation",
     *      tags={"Población"},
     *      summary="Obtener población por municipios",
     *      description="Devuelve datos de población agrupados por municipio. Por defecto ordenados alfabéticamente. Soporta múltiples filtros que pueden combinarse.",
     *      @OA\Parameter(
     *          name="municipio",
     *          in="query",
     *          description="Filtrar por nombre de municipio (búsqueda parcial, case-insensitive)",
     *          required=false,
     *          @OA\Schema(type="string", example="Santa Cruz")
     *      ),
     *      @OA\Parameter(
     *          name="genero",
     *          in="query",
     *          description="Filtrar por género",
     *          required=false,
     *          @OA\Schema(type="string", enum={"Hombres", "Mujeres"}, example="Hombres")
     *      ),
     *      @OA\Parameter(
     *          name="edad",
     *          in="query",
     *          description="Filtrar por edad o rango de edad específico",
     *          required=false,
     *          @OA\Schema(type="string", example="De 0 a 14 años")
     *      ),
     *      @OA\Parameter(
     *          name="edad_min",
     *          in="query",
     *          description="Edad mínima (filtra rangos que se solapan)",
     *          required=false,
     *          @OA\Schema(type="integer", example=18)
     *      ),
     *      @OA\Parameter(
     *          name="edad_max",
     *          in="query",
     *          description="Edad máxima (filtra rangos que se solapan)",
     *          required=false,
     *          @OA\Schema(type="integer", example=65)
     *      ),
     *      @OA\Parameter(
     *          name="ano",
     *          in="query",
     *          description="Filtrar por año (puede usarse múltiples veces)",
     *          required=false,
     *          @OA\Schema(type="integer", example=2024)
     *      ),
     *      @OA\Parameter(
     *          name="ano_desde",
     *          in="query",
     *          description="Año inicial del rango",
     *          required=false,
     *          @OA\Schema(type="integer", example=2022)
     *      ),
     *      @OA\Parameter(
     *          name="ano_hasta",
     *          in="query",
     *          description="Año final del rango",
     *          required=false,
     *          @OA\Schema(type="integer", example=2024)
     *      ),
     *      @OA\Parameter(
     *          name="order_by",
     *          in="query",
     *          description="Campo para ordenar los resultados",
     *          required=false,
     *          @OA\Schema(type="string", enum={"municipio", "total_poblacion"}, default="municipio")
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          in="query",
     *          description="Dirección del ordenamiento",
     *          required=false,
     *          @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Lista de municipios con población",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/MunicipioResponse")),
     *              @OA\Property(property="ultimo_ano", type="integer", example=2025, description="Último año con datos disponibles"),
     *              @OA\Property(property="total", type="integer", example=2267239, description="Población total del último año")
     *          )
     *      ),
     *      @OA\Response(response=400, description="Parámetros inválidos"),
     *      @OA\Response(response=500, description="Error interno del servidor")
     * )
     */

    /**
     * @OA\Get(
     *      path="/api/population/islands",
     *      operationId="getIslandPopulation",
     *      tags={"Población"},
     *      summary="Obtener población por islas",
     *      description="Devuelve datos de población agrupados por isla. Puede retornar totales por isla o desglose por municipios dentro de cada isla.",
     *      @OA\Parameter(
     *          name="breakdown",
     *          in="query",
     *          description="Desglose por municipio (true para incluir municipios de cada isla)",
     *          required=false,
     *          @OA\Schema(type="string", enum={"true", "false"}, example="true")
     *      ),
     *      @OA\Parameter(
     *          name="genero",
     *          in="query",
     *          description="Filtrar por género",
     *          required=false,
     *          @OA\Schema(type="string", enum={"Hombres", "Mujeres"})
     *      ),
     *      @OA\Parameter(
     *          name="edad",
     *          in="query",
     *          description="Filtrar por edad",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="ano",
     *          in="query",
     *          description="Filtrar por año",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="order_by",
     *          in="query",
     *          description="Campo para ordenar",
     *          required=false,
     *          @OA\Schema(type="string", enum={"isla", "total_poblacion"}, default="isla")
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          in="query",
     *          description="Dirección del ordenamiento",
     *          required=false,
     *          @OA\Schema(type="string", enum={"asc", "desc"}, default="asc")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Datos de población por isla",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/IslaResponse")),
     *              @OA\Property(property="ultimo_ano", type="integer", example=2025, description="Último año con datos disponibles"),
     *              @OA\Property(property="total", type="integer", example=2267239, description="Población total del último año")
     *          )
     *      )
     * )
     */

    /**
     * @OA\Get(
     *      path="/api/population/evolution",
     *      operationId="getPopulationEvolution",
     *      tags={"Población"},
     *      summary="Obtener evolución de población",
     *      description="Devuelve la evolución de población año a año en porcentajes y totales. Puede retornar datos de un municipio, una isla, o una isla desglosada por municipios.",
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="Tipo de entidad",
     *          required=true,
     *          @OA\Schema(type="string", enum={"municipality", "island"})
     *      ),
     *      @OA\Parameter(
     *          name="id",
     *          in="query",
     *          description="ID de la entidad (lugar_id o isla_id)",
     *          required=true,
     *          @OA\Schema(type="integer", example=72)
     *      ),
     *      @OA\Parameter(
     *          name="breakdown",
     *          in="query",
     *          description="Desglose por municipio (solo válido cuando type=island)",
     *          required=false,
     *          @OA\Schema(type="string", enum={"true", "false"})
     *      ),
     *      @OA\Parameter(
     *          name="genero",
     *          in="query",
     *          description="Filtrar por género",
     *          required=false,
     *          @OA\Schema(type="string", enum={"Hombres", "Mujeres"})
     *      ),
     *      @OA\Parameter(
     *          name="edad",
     *          in="query",
     *          description="Filtrar por edad",
     *          required=false,
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="ano",
     *          in="query",
     *          description="Filtrar por año",
     *          required=false,
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Evolución de población",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="entity_type", type="string", example="municipality"),
     *              @OA\Property(property="entity_id", type="integer", example=72),
     *              @OA\Property(property="entity_name", type="string", example="Santa Cruz de Tenerife"),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/EvolutionItem")),
     *              @OA\Property(property="total_inicial", type="integer", example=665097),
     *              @OA\Property(property="total_final", type="integer", example=678727)
     *          )
     *      ),
     *      @OA\Response(response=400, description="Parámetros requeridos faltantes"),
     *      @OA\Response(response=404, description="Municipio o isla no encontrada")
     * )
     */

    /**
     * @OA\Get(
     *      path="/api/population/search",
     *      operationId="searchPopulation",
     *      tags={"Población"},
     *      summary="Buscar municipios o islas",
     *      description="Realiza una búsqueda flexible de municipios e islas usando un término de búsqueda. La búsqueda es case-insensitive y retorna resultados ordenados alfabéticamente.",
     *      @OA\Parameter(
     *          name="q",
     *          in="query",
     *          description="Término de búsqueda (mínimo 2 caracteres)",
     *          required=true,
     *          @OA\Schema(type="string", example="Santa", minLength=2)
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          in="query",
     *          description="Tipo de entidad a buscar",
     *          required=false,
     *          @OA\Schema(type="string", enum={"all", "municipalities", "islands"}, default="all")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Resultados de búsqueda",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="query", type="string", example="Santa"),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/SearchResult"))
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="El término de búsqueda debe tener al menos 2 caracteres"
     *      )
     * )
     */

    /**
     * @OA\Get(
     *      path="/api/population/filters",
     *      operationId="getAvailableFilters",
     *      tags={"Población"},
     *      summary="Obtener filtros disponibles",
     *      description="Retorna los valores disponibles para cada tipo de filtro: género, edad, y años. Útil para construir interfaces de usuario con opciones válidas.",
     *      @OA\Response(
     *          response=200,
     *          description="Filtros y valores disponibles",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean",
     *                  example=true
     *              ),
     *              @OA\Property(
     *                  property="filters",
     *                  type="object",
     *                  @OA\Property(
     *                      property="genero",
     *                      type="array",
     *                      @OA\Items(type="string"),
     *                      example={"Hombres", "Mujeres"}
     *                  ),
     *                  @OA\Property(
     *                      property="edad",
     *                      type="array",
     *                      @OA\Items(type="string"),
     *                      description="Incluye edades individuales y rangos"
     *                  ),
     *                  @OA\Property(
     *                      property="anos",
     *                      type="array",
     *                      @OA\Items(type="integer"),
     *                      example={2021, 2022, 2023, 2024, 2025}
     *                  )
     *              )
     *          )
     *      )
     * )
     */
}
