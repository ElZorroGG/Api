<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Isla;
use App\Models\Lugar;
use App\Models\PopulationStat;
use Illuminate\Http\Request;

class PopulationController extends Controller
{
    /**
     * Obtener datos de población por municipio
     * GET /api/population/municipalities
     */
    public function getMunicipalityPopulation(Request $request)
    {
        $query = PopulationStat::query()
            ->with(['lugar' => fn($q) => $q->select('id', 'nombre', 'isla_id')])
            ->whereNotNull('lugar_id');

        // Aplicar filtros
        $this->applyFilters($query, $request);

        $data = $query->groupBy('lugar_id')
            ->selectRaw('lugar_id, SUM(poblacion) as total_poblacion')
            ->with('lugar')
            ->get()
            ->map(fn($item) => [
                'lugar_id' => $item->lugar_id,
                'municipio' => $item->lugar->nombre ?? 'N/A',
                'isla_id' => $item->lugar->isla_id,
                'total_poblacion' => $item->total_poblacion,
            ]);

        // Ordenar alfabéticamente por defecto
        $orderBy = $request->get('order_by', 'municipio');
        $order = $request->get('order', 'asc');
        
        if ($orderBy === 'municipio') {
            $data = $data->sortBy(fn($item) => $item['municipio'], SORT_REGULAR, $order === 'desc');
        } elseif ($orderBy === 'total_poblacion') {
            $data = $data->sortBy(fn($item) => $item['total_poblacion'], SORT_NUMERIC, $order === 'desc');
        }

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'total' => $data->sum('total_poblacion'),
        ]);
    }

    /**
     * Obtener datos de población por isla
     * GET /api/population/islands
     */
    public function getIslandPopulation(Request $request)
    {
        $byMunicipality = $request->get('breakdown') === 'true';

        if ($byMunicipality) {
            return $this->getIslandPopulationByMunicipality($request);
        }

        $query = PopulationStat::query();
        $this->applyFilters($query, $request);

        $data = $query->groupBy('isla_id')
            ->selectRaw('isla_id, SUM(poblacion) as total_poblacion')
            ->with(['isla' => fn($q) => $q->select('id', 'nombre')])
            ->whereNotNull('isla_id')
            ->get()
            ->map(fn($item) => [
                'isla_id' => $item->isla_id,
                'isla' => $item->isla->nombre ?? 'N/A',
                'total_poblacion' => $item->total_poblacion,
            ]);

        // Ordenar alfabéticamente por defecto
        $orderBy = $request->get('order_by', 'isla');
        $order = $request->get('order', 'asc');
        
        if ($orderBy === 'isla') {
            $data = $data->sortBy(fn($item) => $item['isla'], SORT_REGULAR, $order === 'desc');
        } elseif ($orderBy === 'total_poblacion') {
            $data = $data->sortBy(fn($item) => $item['total_poblacion'], SORT_NUMERIC, $order === 'desc');
        }

        return response()->json([
            'success' => true,
            'data' => $data->values(),
            'total' => $data->sum('total_poblacion'),
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

        $this->applyFilters($query, $request);

        $data = $query->groupBy(['isla_id', 'lugar_id'])
            ->selectRaw('isla_id, lugar_id, SUM(poblacion) as total_poblacion')
            ->get()
            ->groupBy(fn($item) => $item->isla->nombre ?? 'N/A')
            ->map(function($municipios, $islandName) {
                return [
                    'isla' => $islandName,
                    'municipios' => $municipios->map(fn($m) => [
                        'municipio' => $m->lugar->nombre ?? 'N/A',
                        'total_poblacion' => $m->total_poblacion,
                    ])->sortBy('municipio')->values(),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $data->sortBy('isla')->values(),
            'total' => PopulationStat::sum('poblacion'),
        ]);
    }

    /**
     * Obtener evolución de población (porcentajes y totales)
     * GET /api/population/evolution
     */
    public function getPopulationEvolution(Request $request)
    {
        $type = $request->get('type', 'municipality'); // municipality o island
        $entityId = $request->get('id'); // lugar_id o isla_id

        if (!$entityId) {
            return response()->json(['error' => 'ID is required'], 400);
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
                'poblacion' => $item->poblacion,
                'porcentaje_cambio' => round((($item->poblacion - $firstPopulation) / $firstPopulation) * 100, 2),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $evolution,
            'total_inicial' => $firstPopulation,
            'total_final' => $data->last()->poblacion,
        ]);
    }

    /**
     * Buscar municipios o islas
     * GET /api/population/search?q=Santa
     */
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

    /**
     * Obtener filtros disponibles
     * GET /api/population/filters
     */
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

        // Filtro por rango de edad (simplificado)
        if ($request->has('edad_min') && $request->has('edad_max')) {
            // Aquí puedes implementar lógica más compleja si los datos lo permiten
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
}
