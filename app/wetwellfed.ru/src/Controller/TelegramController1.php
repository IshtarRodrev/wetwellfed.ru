<?php

namespace App\Controller;

use App\Entity\Eater;
use App\Form\MealAddType;
use App\Repository\EaterRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\MealRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Core\Security;
use App\Form\HomeMealAddType;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TelegramController1 extends AbstractController
{
    private $twig;
    private $entityManager;
    private $bus;
    private string $botToken = "6110747918:AAGlaCai9BXon-soaDAmYsRD3jAW03J1jaQ";
    public string $nickname = "human";
    public $eater;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager, MessageBusInterface $bus)
    {
        $this->twig = $twig; // избавляемся от дублирования Environment $twig в методах
        $this->entityManager = $entityManager;
        $this->bus = $bus;
    }

    /**
     * @Route("/webhook1", name="tg_webhook1")
     * @return Response
     */
    public function webhook(Security $security, Request $request, LoggerInterface $logger, EaterRepository $eaterRepository, ManagerRegistry $doctrine): Response
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        if ($data){

            $from = $data['callback_query']['from']['id'];
            // get Eater object
            $entityManager = $doctrine->getManager();
            $eater = $entityManager->getRepository(Eater::class)
                ->findOneBy(['telegram_id' => $from]);
        }
        $logger->debug('Telegram webhook response: ', [$data]);

        // TODO: если пришло сообщение начинающееся со /start, то распарсить хеш
        if ($data['message']['text'])
        {
            $chat_id = $data['message']['from']['id']; // UNDEFINED BECAUSE OF EMPTY TOKEN

            $messageText = preg_split("/[ ]+/", $data['message']['text']);
            $logger->debug('$messageText', $messageText); //OR next($messageText)
            $logger->debug('$data["message"]["text"]', [$data['message']['text']]); //OR next($messageText)
            $text = "Sorry, can`t find your info. \n\nIt might be due to some mistake or you just didn`t login using the site. This bot belongs to wetwellfed.ru and it can`t itself process any data yet. \n \nPlease make sure you`re authorised using the link from the site wetwellfed.ru and try again.";
            $reply = array(
                'inline_keyboard' => array(
                    array(
                        array(
                            'text' => 'To the site!',
                            'callback_data' => 'test_1',
                            'url' => 'https://wetwellfed.ru/'
                        ),
                    )
                ),
            );

            // TODO: если пришло сообщение начинающееся со /start, то распарсить хеш
            //	        1) по хешу понять юзер_ид(достав из кеша)
            //	        2) юзеру в табличку задаёшь telegram_id
            //  (в виде hash => user_id)
            if ($messageText === [0 => "today's", 1 => "status"]){
                $text = "your energy score";
            }
            if (isset($messageText[1])){ // $authHash = $messageText[1] = 65346331805633113aedb9d9f70334ef
                $authHash = $messageText[1];
                $logger->debug('Telegram webhook response: HASH RECEIVED', [$messageText[1]]); //OR next($messageText)

                // get site id from hash
                $cachePool = new FilesystemAdapter();
                $cacheItem = $cachePool->getItem($authHash);

                $id = -1;
                if ($cacheItem->IsHit()) {

                    $id = $cacheItem->get(); //???????
                    $logger->debug('IDENTIFIER CHECK ATTEMPT: ', [$id]);
                    // это получить текущего авторизированного пользоваться для текущего СОЕДИНЕНИЯ
                    // а текущее соеденение - это вебхук от телеграма к серверу
                    // если бы телеграм там как-то авторизовывался у тебя на сайте, то ещё бы это работало
                    // но сейчас ведь нифига подобного нет
                    // сейчас простой вебхук от телеграма
                    //$id = $security->getUser()->getUserIdentifier();
                    //$logger->debug('IDENTIFIER CHECK ATTEMPT: ', [$id]);
                } else {
                    $logger->debug('NOT FOUND CACHE FOR AUTH_TOKEN: ', [$authHash]);
                }

                if ($id != -1) {
                    // get Eater object
                    $entityManager = $doctrine->getManager();
                    $eater = $entityManager->getRepository(Eater::class)->find($id);
                    $this->eater = $eater;

                    $logger->debug('EATER DATA FOUND: ', [$eater]);

                    if ($eater) {
                        $eater->setTelegram_id($chat_id);
                        $entityManager->flush();

                        $logger->debug('TG ID HAS BEEN RECORDED TO DATABASE');

                        $nickname = $eater->getName();
                        $this->nickname = $nickname;
                        $text = "Thank you for trying out our free telegram bot.\n Here you just check your daily calories, more functions will be introduced soon.\n \n So, what should I show you next?";
                        $reply = array(
                            'keyboard' => array(
                                array(
                                    array(
                                        'text' => "today's status",
                                        'callback_data' => 'test_1',
                                    ),
                                    array(
                                        'text' => "week history",
                                        'callback_data' => 'test_2',
                                    )
                                )
                            ),
                            'one_time_keyboard' => TRUE,
                            'resize_keyboard'   => TRUE,
                        );
                    } else {
                        $logger->debug('EATER NOT FOUND', [$id]);
                    }
                }
            }
            if ($messageText[0] === '/start'){
//                $chat_id = $data['message']['from']['id'];
                $username = $data['message']['from']['first_name']; // TG nickname
                $username = "human";


                $ch = curl_init("https://api.telegram.org/bot". $this->botToken ."/sendMessage?" .
                    http_build_query($getQuery));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_HEADER, false);

                $resultQuery = curl_exec($ch);
                curl_close($ch);
                exit();
            }
            elseif($messageText[0] === '/start'){
                // приветственное
                if (isset($messageText[1])){

                    $authHash = $messageText[1];
                    $logger->debug('Telegram webhook response: HASH RECEIVED', [$messageText[1]]); //OR next($messageText)

                    // get site id from hash
                    $cachePool = new FilesystemAdapter();
                    $cacheItem = $cachePool->getItem($authHash);

                    $id = -1;
                    if ($cacheItem->IsHit()) {

                        $id = $cacheItem->get(); //???????
                        $logger->debug('IDENTIFIER CHECK ATTEMPT: ', [$id]);
                        // это получить текущего авторизированного пользоваться для текущего СОЕДИНЕНИЯ
                        // а текущее соеденение - это вебхук от телеграма к серверу
                        // если бы телеграм там как-то авторизовывался у тебя на сайте, то ещё бы это работало
                        // но сейчас ведь нифига подобного нет
                        // сейчас простой вебхук от телеграма
                        //$id = $security->getUser()->getUserIdentifier();
                        //$logger->debug('IDENTIFIER CHECK ATTEMPT: ', [$id]);
                    } else {
                        $logger->debug('NOT FOUND CACHE FOR AUTH_TOKEN: ', [$authHash]);
                    }

                    if ($id != -1) {
                        // get Eater object
                        $entityManager = $doctrine->getManager();
                        $eater = $entityManager->getRepository(Eater::class)->find($id);
                        $this->eater = $eater;

                        $logger->debug('EATER DATA FOUND: ', [$eater]);

                        if ($eater) {
                            $eater->setTelegram_id($chat_id);
                            $entityManager->flush();

                            $logger->debug('TG ID HAS BEEN RECORDED TO DATABASE');

                            $nickname = $eater->getName();
                            $this->nickname = $nickname;
                            $text = "Thank you for trying out our free telegram bot.\n Here you just check your daily calories, more functions will be introduced soon.\n \n So, what should I show you next?";
                            $reply = array(
                                'keyboard' => array(
                                    array(
                                        array(
                                            'text' => "today's status",
                                            'callback_data' => 'test_1',
                                        ),
                                        array(
                                            'text' => "week history",
                                            'callback_data' => 'test_2',
                                        )
                                    )
                                ),
                                'one_time_keyboard' => TRUE,
                                'resize_keyboard'   => TRUE,
                            );
                        } else {
                            $logger->debug('EATER NOT FOUND', [$id]);
                        }
                    }
                }
                else {
                    // приветственное вступительное
                }
            }
        }
        var_dump($data);
        exit();
    }

    /**
     * @Route("/test1", name="tg_test1")
     * @return void
     */
    public function test1(Security $security, Request $request, EaterRepository $eaterRepository, MealRepository $mealRepository, ManagerRegistry $doctrine): void
    {
//        $this->sendMedia();
//        $this->sendFile();
//        die;

        $getQuery = array(
            "chat_id" 	=> -1001953191834,
            "text"  	=> "pov: u got banned!",
            "parse_mode" => "html",
            'reply_markup' => json_encode(array(
                'inline_keyboard' => array(
                    array(
                        array(
                            'text' => 'WTF?',
                            'callback_data' => 'test_1',
                        ),
                        array(
                            'text' => 'lol ok)',
                            'callback_data' => 'test_2',
                        ),
                    )
                ),
            )),
//            "reply_to_message_id" => 7,
        );
        $ch = curl_init("https://api.telegram.org/bot". $this->botToken ."/sendMessage?" .
            http_build_query($getQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $resultQuery = curl_exec($ch);
        curl_close($ch);

        echo $resultQuery;
        exit();
    }

    /**
     * @Route("/telegram/auth1", name="teletram_auth1")
     * @return Response
     */
    public function authorize1(Security $security, Request $request, UserPasswordHasherInterface $passwordHasher, EaterRepository $eaterRepository, MealRepository $mealRepository, ManagerRegistry $doctrine): Response
    {
        $cachePool = new FilesystemAdapter();

        $userId = $security->getUser()->getId();
        $authToken = md5($userId + time());

        $cacheItem = $cachePool->getItem($authToken);
        if (!$cacheItem->isHit()) {
            $cacheItem->set($userId); // set value
            $cacheItem->expiresAfter(60 * 15); // 15 minutes;
            $cachePool->save($cacheItem);
        }

        return $this->redirect('https://t.me/Nutrifier_bot?start=' . $authToken);
    }

    public function deleteTGmsg(Security $security, Request $request, EaterRepository $eaterRepository, MealRepository $mealRepository, ManagerRegistry $doctrine): Response
    {
        $this->botToken = "6110747918:AAGlaCai9BXon-soaDAmYsRD3jAW03J1jaQ";

        $getQuery = array(
            "chat_id" 	=> -1001953191834,
            "text"  	=> "POW: you got banned",
            "parse_mode" => "html",
//            "reply_to_message_id" => 7,
        );
        $ch = curl_init("https://api.telegram.org/bot". $this->botToken ."/sendMessage?" .
            http_build_query($getQuery));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        $resultQuery = curl_exec($ch);
        curl_close($ch);

        echo $resultQuery;
        exit();
    }

    public function sendMedia(): void
    {
        $arrayQuery = array(
            'chat_id' 	=> -1001953191834,
            'caption' => "It's five o`clock, bitch.",
            'animation' => "https://wetwellfed.ru/tea.gif",
//            'photo' => curl_file_create(__DIR__ . '/../../public/tea.gif', 'image/gif' , 'tea.gif'),
            'reply_markup' => json_encode(array(
                'inline_keyboard' => array(
                    array(
                        array(
                            'text' => '☕',
                            'callback_data' => 'test_1',
                        ),

                        array(
                            'text' => '🍵',
                            'callback_data' => 'test_2',
                        ),
                    )
                ),
            )),
            "has_spoiler" => true,
        );
        $ch = curl_init('https://api.telegram.org/bot'. $this->botToken .'/sendAnimation');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayQuery);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
        curl_close($ch);

        echo $res;
    }

    public function sendFile(): void
    {
        $chat_id = -1001953191834;
        $filePath = realpath("index.php");

        $arrayQuery = array(
            'chat_id' 	 => -1001953191834,
            'caption'    => "вот так",
            "parse_mode" => "html",
            //'document'   => "https://wetwellfed.ru/tea.gif",
//            'photo'    => curl_file_create(__DIR__ . '/../../public/tea.gif', 'image/gif' , 'tea.gif'),
            'reply_markup' => json_encode(array(
                'inline_keyboard' => array(
                    array(
                        array(
                            'text' => '1',
                            'callback_data' => 'test_1',
                        ),

                        array(
                            'text' => '2',
                            'callback_data' => 'test_2',
                        ),
                    )
                ),
            )),
            "has_spoiler" => true,
        );

        $ch = curl_init("https://api.telegram.org/bot". $this->botToken ."/sendDocument");
        curl_setopt($ch, CURLOPT_URL, "https://api.telegram.org/bot".$this->botToken."/sendDocument"); // как видишь, мы не передаём данные через гет
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1); // в этой строчке мы говорим, что будем делать запрос как ПОСТ. по дефолту курл запрос гет
        // мы сказали что будет пост запрос

        // Create CURLFile
        $finfo = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filePath);
        $cFile = new \CURLFile($filePath, $finfo);// using $arrayQuery['document'] as FILENAME
        $arrayQuery["document"] = $cFile;

        // как понимаю, если ты отпарвляешь через пост хоть что-то, то будь добр всю информацию передай через пост а не гет
        // понятно?
        // не понятно что именно ты сделал
        // честно говоря не могу пока различить пост от гета здесь
        // сейчас понятнее стало?
        // нет. Всё ещё не понимаю где что


        // здесь задаём данные, которые хотим отправить пост запросом
        // эти данные будут записаны не в урл(как в случае гет запроса), а будут записаны в поток инпута
        // на сервере телеграм, эти данные будут доступны по переменно $_POST['chat_id'] и $_FILE
        curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayQuery); // мы все данные передаём через пост. картинку и все остальные данные

        // Call
        $result = curl_exec($ch);

        // Show result and close curl
        var_dump($result);
        curl_close($ch);
    }
}


//phpinfo();
//die();

//<?php
// try {
//     // Проверить адрес электронной почты
//     $email = filter_input(INPUТ_POST, 'email', FILТER_VALIDAТE_EМAIL);
//     if (!$email) {
//         throw new Exception ('Неправильный адрес электронной почты');
//     }
//
//     // Проверить пароль
//     $password = filter_input(INPUT_POST, 'password');
//     if (!$password || mb_strlen($password) < 8) {
//         throw new Exception ('Пароль должен содержать не менее 8 символов');
//     }
//
//     // Создать хеш пароля
//     $passwordHash = password_hash(
//         $password,
//         PASSWORD_DEFAULT,
//         [' cost' => 12]
//     );
//     if ($passwordHash === false) {
//         throw new Exception ('Ошибка при хешировании пароля');
//     }
//
//     // Создать учетную запись пользователя (ЭТО ПСЕВДОКОД)
//     $user = new User();
//     $user->email = $email;
//     $user->password_hash = $passwordHash;
//     $user->save();
//
//     // Перенаправить на страницу входа
//     header('HTTP/1.1 302 Redirect');
//     header('Location: / login.php');
// }
//catch (Exception $e) {
//    // Отчет об ошибке
//    header ('HTTP/1.1 400 Bad request');
//    echo $e->getMessage ( ) ;
// }