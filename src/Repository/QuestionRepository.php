<?php

namespace App\Repository;

use App\Entity\Question;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Question|null find($id, $lockMode = null, $lockVersion = null)
 * @method Question|null findOneBy(array $criteria, array $orderBy = null)
 * @method Question[]    findAll()
 * @method Question[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuestionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Question::class);
    }

    /**
     * Find all questions from the same quiz with an order value that is strictly greater than the provided question
     *
     * @param Question $question
     * @return Question[]
     */
    public function findInSameQuizWithGreaterOrder(Question $question, ?int $targetOrder = null): array
    {
        // Si on n'est pas dans un cas de réordonnement
        if (is_null($targetOrder)) {
            $minOrder = $question->getOrder();
            $orderBy = 'ASC';
        // Sinon
        } else {
            // Détermine la valeur la plus faible et la valeur plus forte entre l'ordre initial de la question et l'ordre désiré
            if ($targetOrder > $question->getOrder()){
                $minOrder = $question->getOrder();
                $maxOrder = $targetOrder;
                $orderBy = 'ASC';
            } else {
                $minOrder = $targetOrder;
                $maxOrder = $question->getOrder();
                $orderBy = 'DESC';
            }
        }
        
        // Cherche toutes les questions...
        $query = $this->createQueryBuilder('q')
            // ...appartenant au même quiz...
            ->where('q.quiz = :quiz')
            ->setParameter('quiz', $question->getQuiz())
            // ...différentes de la question concernée...
            ->andWhere('q.id != :id')
            ->setParameter('id', $question->getId())
            // ...avec une valeur d'ordre supérieure ou égale à la valeur minimum déterminée précédemment
            ->andWhere('q.order >= :minOrder')
            ->setParameter('minOrder', $minOrder)
        ;

        // Si une valeur d'ordre maximum a été déterminée
        if (isset($maxOrder)) {
            // Complète la requête
            $query
                // ...avec une valeur d'ordre inférieure ou égale à la valeur maximum déterminée précédemment
                ->andWhere('q.order <= :maxOrder')
                ->setParameter('maxOrder', $maxOrder)
            ;
        }

        // Compléte et exécute la requête
        return $query
            // ...triées dans l'ordre déterminée précédemment
            ->orderBy('q.order', $orderBy)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Count the questions from the same quiz
     *
     * @param Question $question
     * @return int
     */
    public function countInSameQuiz(Question $question): int
    {
        // Compte les questions...
        return $this->createQueryBuilder('q')
            ->select('COUNT(q.id)')
            ->where('q.quiz = :quiz')
            // ...appartenant au même quiz
            ->setParameter('quiz', $question->getQuiz())
            ->getQuery()
            // Récupère le résultat de la requête sous forme de nombre
            ->getSingleScalarResult()
        ;
    }

    // /**
    //  * @return Question[] Returns an array of Question objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('q.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Question
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
