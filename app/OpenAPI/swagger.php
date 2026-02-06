<?php

namespace App\OpenAPI;

/**
 * @OA\Info(
 *      title="API de Población - Canarias",
 *      version="1.0.0",
 *      description="API REST para consultar datos demográficos de población de las Islas Canarias. Proporciona información sobre población por municipios, islas, evolución histórica y opciones avanzadas de búsqueda y filtrado.",
 *      @OA\Contact(
 *          name="API Support",
 *          email="api@canarias.es"
 *      ),
 *      @OA\License(
 *          name="MIT",
 *          url="https://opensource.org/licenses/MIT"
 *      )
 * )
 *
 * @OA\Server(
 *      url="http://localhost:8000",
 *      description="Development Server (Local)"
 * )
 *
 * @OA\Tag(
 *      name="Población",
 *      description="Endpoints para consultar datos de población"
 * )
 *
 * @OA\Schema(
 *      schema="MunicipioResponse",
 *      type="object",
 *      @OA\Property(property="lugar_id", type="integer", example=72, description="ID único del municipio"),
 *      @OA\Property(property="municipio", type="string", example="Santa Cruz de Tenerife", description="Nombre del municipio"),
 *      @OA\Property(property="isla_id", type="integer", example=5, description="ID de la isla a la que pertenece"),
 *      @OA\Property(property="total_poblacion", type="integer", example=678727, description="Población total del municipio")
 * )
 *
 * @OA\Schema(
 *      schema="IslaResponse",
 *      type="object",
 *      @OA\Property(property="isla_id", type="integer", example=5, description="ID único de la isla"),
 *      @OA\Property(property="isla", type="string", example="Tenerife", description="Nombre de la isla"),
 *      @OA\Property(property="total_poblacion", type="integer", example=928212345, description="Población total de la isla")
 * )
 *
 * @OA\Schema(
 *      schema="EvolutionItem",
 *      type="object",
 *      @OA\Property(property="ano", type="integer", example=2021, description="Año del registro"),
 *      @OA\Property(property="poblacion", type="integer", example=665097, description="Población en ese año"),
 *      @OA\Property(property="porcentaje_cambio", type="number", format="float", example=0, description="Porcentaje de cambio respecto al primer año")
 * )
 *
 * @OA\Schema(
 *      schema="SearchResult",
 *      type="object",
 *      @OA\Property(property="id", type="integer", example=72, description="ID de la entidad"),
 *      @OA\Property(property="nombre", type="string", example="Santa Cruz de Tenerife", description="Nombre de la entidad"),
 *      @OA\Property(property="tipo", type="string", example="municipio", description="Tipo de entidad (municipio o isla)"),
 *      @OA\Property(property="isla", type="string", example="Tenerife", description="Nombre de la isla (solo para municipios)", nullable=true)
 * )
 */
class SwaggerAnnotations
{
    /**
     * @OA\Get(
     *      path="/api/population/municipalities",
     *      operationId="getMunicipalityPopulation",
     *      tags={"Población"},
     *      summary="Obtener población por municipios",
     *      description="Devuelve datos de población agrupados por municipio. Por defecto ordenados alfabéticamente. Soporta múltiples filtros que pueden combinarse.",
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
     *              @OA\Property(property="total", type="integer", example=2267239, description="Población total agregada")
     *          )
     *      ),
     *      @OA\Response(response=400, description="Parámetros inválidos"),
     *      @OA\Response(response=500, description="Error interno del servidor")
     * )
     */
    public function municipalities() {}

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
     *              @OA\Property(property="total", type="integer", example=2267239)
     *          )
     *      )
     * )
     */
    public function islands() {}

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
    public function evolution() {}

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
    public function search() {}

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
    public function filters() {}
}
