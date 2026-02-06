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
 *      @OA\Property(property="total_poblacion", type="integer", example=678727, description="Población del municipio en el último año disponible"),
 *      @OA\Property(property="ultimo_ano", type="integer", example=2025, description="Último año con datos disponibles"),
 *      @OA\Property(property="suma_poblacion", type="integer", example=3393635, description="Suma acumulada de población de todos los años")
 * )
 *
 * @OA\Schema(
 *      schema="IslaResponse",
 *      type="object",
 *      @OA\Property(property="isla_id", type="integer", example=5, description="ID único de la isla"),
 *      @OA\Property(property="isla", type="string", example="Tenerife", description="Nombre de la isla"),
 *      @OA\Property(property="total_poblacion", type="integer", example=928604, description="Población de la isla en el último año disponible"),
 *      @OA\Property(property="ultimo_ano", type="integer", example=2025, description="Último año con datos disponibles"),
 *      @OA\Property(property="suma_poblacion", type="integer", example=4643020, description="Suma acumulada de población de todos los años")
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
}
