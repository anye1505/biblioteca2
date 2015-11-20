<?php

namespace Biblioteca\TegBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;


/**
 * tegRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class tegRepository extends EntityRepository
{
	/**
	 * Our new getAllPosts() method
	 *
	 * 1. Create & pass query to paginate method
	 * 2. Paginate will return a `\Doctrine\ORM\Tools\Pagination\Paginator` object
	 * 3. Return that object to the controller
	 *
	 * @param integer $currentPage The current page (passed from controller)
	 * 
	 * @return \Doctrine\ORM\Tools\Pagination\Paginator
	 */
	public function search($data = null, $currentPage = 1)
	{
	    // Create our query
	    $query = $this->createQueryBuilder('t');
    	if (isset($data['q'])) {
            $exprQ = $query->expr()->orX(
                $query->expr()->like('t.cota', "'%".$data['q']."%'"),
                $query->expr()->like('t.titulo', "'%".$data['q']."%'"),
                $query->expr()->like('t.resumen', "'%".$data['q']."%'"),
                $query->expr()->like('t.palabrasClave', "'%".$data['q']."%'")//,
                //$query->expr()->like('t.autores', "'%".$data['q']."%'"),
                //$query->expr()->like('t.tutores', "'%".$data['q']."%'")
            );
        }
        else
        {
            $exprQ = $query->expr()->isNotNull('t.titulo');
        }

        //Si se filtra por Escuela se 
        if (isset($data['escuela'])){
            $exprEscuela = $query->expr()->eq('t.escuela', "'".$data['escuela']."'");}
        else{$exprEscuela = $query->expr()->isNotNull('t.escuela');}

        
        //Si rangos de fechas es ignorado
        if (!isset($data['desde']) && !isset($data['hasta'])){
            $exprInteval= $query->expr()->isNotNull('t.publicacion');}
        else{
            //Si ingreso rango inferior
            if (isset($data['desde'])) {
                $desde = "'".$data['desde']."-1-1'";
            }else{$desde = 't.publicacion';}
            //Si ingreso rango superior
            if (isset($data['hasta'])) {
                $hasta = "'".$data['hasta']."-12-31'";
            }else{$hasta = 't.publicacion';}
            $exprInteval = $query->expr()->between('t.publicacion', $desde, $hasta);
        }
        //Se unen en AND las codiciones
        $condiciones = $query->expr()->andX(
                    $exprEscuela,
                    $exprInteval,
                    $exprQ
        );
        
        $query->where($query->expr()->andX(
            $query->expr()->eq('t.published', '1'),
            $condiciones
            )
        )
        ->orderBy('t.publicacion', 'DESC')->getQuery();

	    // No need to manually get get the result ($query->getResult())

	    $paginator = $this->paginate($query, $currentPage);

	    return $paginator;
	}

	/**
     * Paginator Helper
     *
     * Pass through a query object, current page & limit
     * the offset is calculated from the page and limit
     * returns an `Paginator` instance, which you can call the following on:
     *
     *     $paginator->getIterator()->count() # Total fetched (ie: `5` posts)
     *     $paginator->count() # Count of ALL posts (ie: `20` posts)
     *     $paginator->getIterator() # ArrayIterator
     *
     * @param Doctrine\ORM\Query $dql   DQL Query Object
     * @param integer            $page  Current page (defaults to 1)
     * @param integer            $limit The total number per page (defaults to 5)
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function paginate($dql, $page = 1, $limit = 5)
    {
        $paginator = new Paginator($dql);

        $paginator->getQuery()
            ->setFirstResult($limit * ($page - 1)) // Offset
            ->setMaxResults($limit); // Limit

        return $paginator;
    }
}
