<?php

namespace App\Controller;

use App\Entity\Eater;
use App\Entity\Meal;
use App\Repository\EaterRepository;
use App\Repository\MealRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Messenger\MessageBusInterface;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;

use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class TelegramController extends AbstractController
{
    private string $botToken = "6110747918:AAGlaCai9BXon-soaDAmYsRD3jAW03J1jaQ";

    /**
     * @Route("/webhook", name="tg_webhook")
     * @return Response
     */
    public function webhook(Request $request, LoggerInterface $logger, EaterRepository $eaterRepository, ManagerRegistry $doctrine): Response
    {
        $data = file_get_contents('php://input');
        $data = json_decode($data, true);

        if (!$data) {
            throw $this->createAccessDeniedException();
        }
        $logger->debug('Telegram webhook response: ', [$data]);

        $reply = []; // TODO: Prepare array to answer
        $nickname = 'human';
        $chat_id = -1;
        if (isset($data['message']) && isset($data['message']['from']) && isset($data['message']['from']['id'])) {
            $chat_id = $data['message']['from']['id'];

            $entityManager = $doctrine->getManager();
            $eater = $entityManager->getRepository(Eater::class)
                ->findOneBy(['telegram_id' => $chat_id]);
            $nickname = $eater ? $eater->getName() : 'human';

            if (isset($data['message']['text'])) {
                $messageText = preg_split("/[ ]+/", $data['message']['text']);
                $logger->debug('$messageText', $messageText); //OR next($messageText)
                $logger->debug('$data["message"]["text"]', [$data['message']['text']]);

                if ($eater && $data['message']['text'] == "today's status") {
                    $daily = $eater->getKcalDayNorm();
                    $score = "";
                    $day = $entityManager->getRepository(Meal::class)
                        ->getHistory($eater, 0);
                    $logger->debug('TODAY SCORE ', $day);
                    foreach ($day as $today) {
                        $score .= $today['kcal'];
                        $percentage = $score * $daily / 100;
                        $result = "$score out of $daily kcal ($percentage%) \n";
                    }

                    $reply = array(
                        "chat_id" => $chat_id, // where message goes to
                        "text" => "Calculating your current energy score, $nickname... 🤖 \n $result ",
                        "parse_mode" => "html",
                        'reply_markup' => json_encode(array(
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
                            'resize_keyboard' => TRUE,
                        )),
                    );
                    $logger->debug('KCAL FOR CURRENT DAY REQUIRED. ', $messageText);
                } // TODO: RECORD REPLY TO BTN_1
                elseif ($eater && $data['message']['text'] == "week history") {
                    $daily = $eater->getKcalDayNorm();
                    $result = "";
                    $day = $entityManager->getRepository(Meal::class)
                        ->getHistory($eater, 7);
                    foreach ($day as $today) {
                        $score = $today['kcal'];
                        $tmp = date_create($today['date']);
                        $date = date_format($tmp, 'm.d l');
                        $percentage = $score * $daily / 100;
                        $result .= "$date - $score out of $daily kcal ($percentage%) \n";
                    }
                    $reply = array(
                        "chat_id" => $chat_id,
                        "text" => "Loading your consumption history for last 7 days, $nickname... 🤖 \n $result ",
                        "parse_mode" => "html",
                        'reply_markup' => json_encode(array(
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
                            'resize_keyboard' => TRUE,
                        )),
                    );
                    $logger->debug('LAST WEEK HISTORY REQUIRED. ', $messageText);
                } // TODO: RECORD REPLY TO BTN_2
                elseif ($messageText[0] === '/start') {
                    // приветственное
                    $logger->debug('START COMMAND CALLED. ', $messageText);
                    if (isset($messageText[1])) {

                        $authHash = $messageText[1];

                        // get site id from hash
                        $cachePool = new FilesystemAdapter();
                        $cacheItem = $cachePool->getItem($authHash);
                        $logger->debug('Telegram webhook response: START COMMAND CALLED. HASH RECEIVED AND READY TO BE CONFIRMED ', [$messageText[1]]); //OR next($messageText)

                        $id = -1;
                        if ($cacheItem->IsHit()) { // && !isset($$eater)

                            $id = $cacheItem->get();
                            $logger->debug('CACHE CONTENTS CONFIRMED HASH_ID ', [$id]);

                            $eater = $entityManager->getRepository(Eater::class)->find($id);
                            $logger->debug('EATER FOUND BY ID: ', [$id]);
                            $nickname = $eater->getName();

                            //записать в базу тг
                            $eater->setTelegram_id($chat_id);
                            $entityManager->flush();
                            $logger->debug('TG ID HAS BEEN RECORDED TO DATABASE');

                            $result = "%first_hello%";
                            $reply = array(
                                "chat_id" => $chat_id, // where message goes to
                                "text" => "Hi,$nickname! Thank you for trying out our very first telegram bot.\n Here you just check your daily calories, more functions will be introduced soon.\n \n So, what should I show you next? 🤖 \n $result ",
                                "parse_mode" => "html",
                                'reply_markup' => json_encode(array(
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
                                    'resize_keyboard' => TRUE,
                                )),
                            );
                        } // TODO: HASHED ID HITS CACHE. FIRST HELLO MESSAGE.
                        else {
                            $logger->debug('ALERT! INCORRECT CACHE CONTENT FOR AUTH_TOKEN: ', [$authHash]);
                            //throw $this->createAccessDeniedException();
                            exit();
                        } // TODO: HASHED ID DOESN'T MATCH CACHE. ERROR.
                    } // TODO: TRYING TO GET HASHED ID
                    elseif ($eater && $eater->getTelegram_id()) {
                        $logger->debug('START COMMAND DUPLICATED ', $messageText);
                        $result = "%hello_again_$nickname%";
                        $reply = array(
                            "chat_id" => $eater->getTelegram_id(), // where message goes to
                            "text" => "Hey, $nickname, what can I do for you? 🤖 \n $result ",
                            "parse_mode" => "html",
                            'reply_markup' => json_encode(array(
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
                                'resize_keyboard' => TRUE,
                            )),
                        );
                    } // TODO: NO HASH ID. TRYING TO GET CURRENT EATER INSTANCE. IGNORE IF EATER ALREADY AUTHENTICATED
                    else {
                        $result = "%user_is_not_eater%";
                        $reply = array(
                            "chat_id" => $chat_id,
                            "text" => "Sorry, $nickname, can`t find your info. \n\n It might be due to some mistake or you just didnt login using the site. This bot belongs to wetwellfed.ru and it can`t itself process any data yet. \n \nPlease make sure you`re authorised using the link from the site wetwellfed.ru and try again. 🤖 \n $result ",
                            "parse_mode" => "html",
                            'reply_markup' => json_encode(array(
                                'inline_keyboard' => array(
                                    array(
                                        array(
                                            'text' => 'To the site!',
                                            'callback_data' => 'test_1',
                                            'url' => 'https://wetwellfed.ru/'
                                        ),
                                    )
                                ),
                                'one_time_keyboard' => TRUE,
                                'resize_keyboard' => TRUE,
                            )),
                        );
                        $logger->debug('START COMMAND CALLED. NO HASH RECEIVED! ', $messageText);
                    } // TODO: HASHED ID IS EMPTY. EATER TG_ID NOT FOUND IN DB. ERROR!
                } // TODO: TRYING TO FIND HASHED ID
                elseif ($eater) {
                    $logger->debug('UNRECOGNIZABLE REQUEST WAS SENT BUY USER', [$data]);
                    $result = "%wrong_request%";
                    $reply = array(
                        "chat_id" => $eater->getTelegram_id(), // where message goes to
                        "text" => "Sorry, $nickname, your request is unable to recognize. Please, pick exactly what can I do for you? 🤖 \n $result ",
                        "parse_mode" => "html",
                        'reply_markup' => json_encode(array(
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
                            'resize_keyboard' => TRUE,
                        )),
                    );
                } // TODO: UNRECOGNIZABLE COMMAND
                else {
                    $logger->debug('UNHANDLED REQUEST1 ', [$data]);
                    exit();
                } // TODO: UNKNOWN COMMAND. UNKNOWN USER. ERROR MESSAGE!
            } else {
                $logger->debug('UNHANDLED REQUEST2 ', [$data]);
                exit();
            }
        }

        // TODO: SET PREPARED REPLY
        $ch = curl_init("https://api.telegram.org/bot" . $this->botToken . "/sendMessage?" .
            http_build_query($reply));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $reply);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $resultQuery = curl_exec($ch);
        curl_close($ch);

        echo $resultQuery;
        exit();
    }

    /**
     * @Route("/telegram/auth", name="teletram_auth")
     * @return Response
     */
    public function authorize(Security $security, Request $request, UserPasswordHasherInterface $passwordHasher, EaterRepository $eaterRepository, MealRepository $mealRepository, ManagerRegistry $doctrine): Response
    {
        $cachePool = new FilesystemAdapter();

        $userId = $security->getUser()->getId();

        $authToken = md5($userId + time());

        $cacheItem = $cachePool->getItem($authToken);
        if (!$cacheItem->isHit()) {
            $cacheItem->set($userId);          // set value
            $cacheItem->expiresAfter(60 * 60); // 1h
            $cachePool->save($cacheItem);
            echo 'https://t.me/Nutrifier_bot?start=' . $authToken;
            exit();
        }

        return $this->redirect('https://t.me/Nutrifier_bot?start=' . $authToken);
    }
}