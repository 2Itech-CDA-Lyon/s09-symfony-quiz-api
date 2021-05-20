<?php

namespace App\DataFixtures;

use App\Entity\Quiz;
use App\Entity\User;
use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{
    private $manager;

    private $passwordEncoder;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function load(ObjectManager $manager)
    {
        // Create users
        $admin = $this->createUser('admin@test.com', 'admin', ['ROLE_ADMIN']);
        $moderator = $this->createUser('moderator@test.com', 'moderator', ['ROLE_MODERATOR']);
        $henriBrice = $this->createUser('henri-brice@test.com', 'henri-brice');
        $adeleRoberte = $this->createUser('adele-roberte@test.com', 'adele-roberte');

        // Create quizzes
        $quiz1 = $this->createQuiz('Divers faits étonnants', 'Etonnez-vous avec ces petites choses de la vie quotidienne que vous ignorez probablement!', 1, $henriBrice);
        $quiz2 = $this->createQuiz('The Big Bang Theory', 'Êtes-vous un vrai fan de The Big Bang Theory? Pour le savoir, un seul moyen: répondez à ce quiz ultime sur la série!', 3, $adeleRoberte);

        // Create questions
        $question11 = $this->createQuestion('Combien de joueurs y a-t-il dans une équipe de football?',	1, $quiz1);
        $question12 = $this->createQuestion('Combien de temps la lumière du soleil met-elle pour nous parvenir?',	2, $quiz1);
        $question13 = $this->createQuestion('En 1582, le pape Grégoire XIII a décidé de réformer le calendrier instauré par Jules César. Mais quel était le premier mois du calendrier julien?',	3, $quiz1);
        $question14 = $this->createQuestion('Lequel de ces signes du zodiaque n\'est pas un signe d\'Eau?',	4, $quiz1);
        $question15 = $this->createQuestion('Combien de doigts ai-je dans mon dos?',	5, $quiz1);
        $question21 = $this->createQuestion('Quel langage fictif Howard parle-t-il?',	1, $quiz2);
        $question22 = $this->createQuestion('Quel est le seul acteur de la série qui possède un doctorat dans la vraie vie?',	2, $quiz2);
        $question23 = $this->createQuestion('Dans quel appartement Penny et Leonard vivent-ils?',	3, $quiz2);
        $question24 = $this->createQuestion('Combien de fois Sheldon doit-il frapper à une porte et dire le nom d\'une personne avant d\'entrer?',	4, $quiz2);
        $question25 = $this->createQuestion('Quel groupe de rock alternatif canadien a créé le générique musical de The Big Bang Theory?',	5, $quiz2);

        // Create answers
        $this->createAnswer('5', $question11);
        $this->createAnswer('7', $question11);
        $question11RightAnswer = $this->createAnswer('11', $question11);
        $this->createAnswer('235', $question11);
        $this->createAnswer('15 secondes', $question12);
        $question12RightAnswer = $this->createAnswer('8 minutes', $question12);
        $this->createAnswer('2 heures', $question12);
        $this->createAnswer('3 mois', $question12);
        $this->createAnswer('Janvier',	$question13);
        $this->createAnswer('Février',	$question13);
        $question13RightAnswer = $this->createAnswer('Mars',	$question13);
        $this->createAnswer('Avril',	$question13);
        $question14RightAnswer = $this->createAnswer('Le Verseau', $question14);
        $this->createAnswer('Le Cancer', $question14);
        $this->createAnswer('Le Scorpion', $question14);
        $this->createAnswer('Les Poissons', $question14);
        $this->createAnswer('2', $question15);
        $this->createAnswer('3', $question15);
        $this->createAnswer('4', $question15);
        $question15RightAnswer = $this->createAnswer('5, comme tout le monde', $question15);
        $this->createAnswer('L\'eflque',	 $question21);
        $this->createAnswer('Le Valyrien',	 $question21);
        $question21RightAnswer = $this->createAnswer('Le Klingon',	 $question21);
        $this->createAnswer('Le Serpentard',	 $question21);
        $this->createAnswer('Kaley Cuoco',	 $question22);
        $question22RightAnswer = $this->createAnswer('Mayim Bialik',	 $question22);
        $this->createAnswer('Johnny Galecki',	 $question22);
        $this->createAnswer('Jim Parsons',	 $question22);
        $this->createAnswer('3A',	 $question23);
        $this->createAnswer('3B',	 $question23);
        $question23RightAnswer = $this->createAnswer('4A',	 $question23);
        $this->createAnswer('4B',	 $question23);
        $this->createAnswer('Une',	 $question24);
        $this->createAnswer('Deux',	 $question24);
        $question24RightAnswer = $this->createAnswer('Trois',	 $question24);
        $this->createAnswer('Quatre',	 $question24);
        $question25RightAnswer = $this->createAnswer('Barenaked Ladies',	 $question25);
        $this->createAnswer('Static in Stereo',	 $question25);
        $this->createAnswer('Brundlefly',	 $question25);

        // Match each question with its right answer
        $question11->setRightAnswer($question11RightAnswer);
        $question12->setRightAnswer($question12RightAnswer);
        $question13->setRightAnswer($question13RightAnswer);
        $question14->setRightAnswer($question14RightAnswer);
        $question15->setRightAnswer($question15RightAnswer);
        $question21->setRightAnswer($question21RightAnswer);
        $question22->setRightAnswer($question22RightAnswer);
        $question23->setRightAnswer($question23RightAnswer);
        $question24->setRightAnswer($question24RightAnswer);
        $question25->setRightAnswer($question25RightAnswer);

        $this->manager->flush();
    }

    protected function createUser(string $email, string $password, array $roles = []): User
    {
        $user = new User();
        $user
            ->setEmail($email)
            ->setPassword($password)
            ->setRoles($roles)
        ;
        $this->manager->persist($user);
        return $user;
    }

    protected function createQuiz(string $title, string $description, int $difficulty, ?User $author): Quiz
    {
        $quiz = new Quiz();
        $quiz
            ->setTitle($title)
            ->setDescription($description)
            ->setDifficulty($difficulty)
            ->setAuthor($author)
        ;
        $this->manager->persist($quiz);
        return $quiz;
    }

    protected function createQuestion(string $text, int $order, Quiz $quiz): Question
    {
        $question = new Question();
        $question
            ->setText($text)
            ->setOrder($order)
            ->setQuiz($quiz)
        ;
        $this->manager->persist($question);
        return $question;
    }

    protected function createAnswer(string $text, Question $question): Answer
    {
        $answer = new Answer();
        $answer
            ->setText($text)
            ->setQuestion($question)
        ;
        $this->manager->persist($answer);
        return $answer;
    }
}
