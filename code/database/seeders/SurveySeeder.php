<?php

namespace Database\Seeders;

use App\Models\Poll;
use App\Services\CustomerLanguageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SurveySeeder extends Seeder
{
    public const SURVEY_ID = 'a148efda-c2d8-48f8-86a2-cc81802a24bb';

    public function run(): void
    {
        $languages = app(CustomerLanguageService::class);
        $defaultLanguage = $languages->default();
        $enabled = array_keys($languages->enabled()) ?: [$defaultLanguage];
        $content = $this->content();
        $translations = [];
        $questions = [];

        foreach (['rating', 'rating', 'yes_no', 'rating', 'choice'] as $index => $type) {
            $questions[$index] = [
                'id' => (string) Str::uuid(),
                'type' => $type,
                'required' => true,
                'threshold' => $type === 'rating' ? 4 : null,
                'texts' => [],
            ];
        }

        foreach ($enabled as $language) {
            $localized = $content[$language] ?? $content['en'];
            $translations[$language] = $localized['email'];
            foreach ($questions as $index => &$question) {
                $text = ['label' => $localized['questions'][$index], 'comment' => ''];
                if ($question['type'] === 'rating') {
                    $text['comment'] = $localized['comment'];
                }
                if ($question['type'] === 'choice') {
                    $text['choices'] = ['Facebook', 'Instagram', 'Google', 'Tripadvisor', ...$localized['choices']];
                }
                $question['texts'][$language] = $text;
            }
            unset($question);
        }

        Poll::firstOrCreate(['id' => self::SURVEY_ID], [
            'label' => 'Sondaggio di gradimento dei clienti',
            'enabled_at' => null,
            'send_after_hours' => 23,
            'review_enabled' => false,
            'review_min_minutes' => 15,
            'review_max_minutes' => 45,
            'default_language' => $defaultLanguage,
            'translations' => $translations,
            'questions' => $questions,
        ]);
    }

    private function content(): array
    {
        return [
            'it' => [
                'email' => [
                    'title' => 'La tua esperienza da @@nomelocale@@',
                    'description' => 'Grazie per il tempo che ci dedichi. Le tue risposte ci aiutano a offrirti un’esperienza migliore.',
                    'subject' => 'Grazie per la visita, @@nomecliente@@!',
                    'message' => 'Ciao @@nomecliente@@, grazie per averci fatto visita da @@nomelocale@@! Ti va di raccontarci com’è andata? Ci vuole circa un minuto e il tuo parere ci aiuterà a migliorare il servizio e l’accoglienza. A presto!',
                    'review_subject' => 'Grazie per il tuo parere, @@nomecliente@@!',
                    'review_message' => 'Ciao @@nomecliente@@, grazie per aver risposto al sondaggio di @@nomelocale@@! Se ti fa piacere, puoi raccontare la tua esperienza anche con una recensione online. Grazie per il tuo tempo e a presto!',
                ],
                'questions' => [
                    'Quanto consiglieresti @@nomelocale@@ ad amici o familiari?',
                    'Come valuti la qualità complessiva dei piatti che hai assaggiato?',
                    'La presentazione dei piatti è stata all’altezza delle tue aspettative?',
                    'Come valuti la cortesia e l’attenzione del personale di sala?',
                    'Come hai conosciuto @@nomelocale@@?',
                ],
                'comment' => 'Cosa ci consigli per migliorare?',
                'choices' => ['Passando davanti al locale', 'Amici o familiari', 'Altro'],
            ],
            'en' => [
                'email' => [
                    'title' => 'Your experience at @@nomelocale@@',
                    'description' => 'Thank you for taking the time to respond. Your feedback helps us give you a better experience.',
                    'subject' => 'Thanks for visiting, @@nomecliente@@!',
                    'message' => 'Hi @@nomecliente@@, thanks for visiting @@nomelocale@@! Would you tell us how it went? It takes about a minute, and your feedback will help us improve our service. We hope to see you again soon!',
                    'review_subject' => 'Thanks for your feedback, @@nomecliente@@!',
                    'review_message' => 'Hi @@nomecliente@@, thanks for completing our survey at @@nomelocale@@! If you’d like to share your experience, we’d love to hear from you in an online review. Thanks for your time, and we hope to see you again soon!',
                ],
                'questions' => [
                    'How likely are you to recommend @@nomelocale@@ to friends or family?',
                    'How would you rate the overall quality of the dishes you tried?',
                    'Did the presentation of the dishes meet your expectations?',
                    'How would you rate the courtesy and attentiveness of the service team?',
                    'How did you hear about @@nomelocale@@?',
                ],
                'comment' => 'What could we do better?',
                'choices' => ['Passing by', 'Friends or family', 'Other'],
            ],
            'de' => [
                'email' => [
                    'title' => 'Ihr Besuch bei @@nomelocale@@',
                    'description' => 'Vielen Dank für Ihre Zeit. Ihre Antworten helfen uns, Ihren nächsten Besuch noch angenehmer zu gestalten.',
                    'subject' => 'Vielen Dank für Ihren Besuch, @@nomecliente@@!',
                    'message' => 'Hallo @@nomecliente@@, vielen Dank für Ihren Besuch bei @@nomelocale@@! Wie hat es Ihnen gefallen? Die Umfrage dauert etwa eine Minute. Ihre Rückmeldung hilft uns, unseren Service zu verbessern. Bis bald!',
                    'review_subject' => 'Vielen Dank für Ihre Rückmeldung, @@nomecliente@@!',
                    'review_message' => 'Hallo @@nomecliente@@, vielen Dank für Ihre Teilnahme an der Umfrage von @@nomelocale@@! Wenn Sie möchten, können Sie Ihre Erfahrungen auch in einer Online-Bewertung teilen. Vielen Dank und bis bald!',
                ],
                'questions' => [
                    'Wie wahrscheinlich würden Sie @@nomelocale@@ Freunden oder Familie empfehlen?',
                    'Wie bewerten Sie die Qualität der Gerichte, die Sie probiert haben?',
                    'Hat die Präsentation der Gerichte Ihre Erwartungen erfüllt?',
                    'Wie bewerten Sie die Freundlichkeit und Aufmerksamkeit des Servicepersonals?',
                    'Wie sind Sie auf @@nomelocale@@ aufmerksam geworden?',
                ],
                'comment' => 'Was können wir verbessern?',
                'choices' => ['Beim Vorbeigehen', 'Freunde oder Familie', 'Sonstiges'],
            ],
            'fr' => [
                'email' => [
                    'title' => 'Votre expérience chez @@nomelocale@@',
                    'description' => 'Merci pour votre temps. Vos réponses nous aident à améliorer votre expérience.',
                    'subject' => 'Merci pour votre visite, @@nomecliente@@ !',
                    'message' => 'Bonjour @@nomecliente@@, merci de votre visite chez @@nomelocale@@ ! Pouvez-vous nous raconter comment cela s’est passé ? Cela prend environ une minute et votre avis nous aidera à améliorer notre service. À bientôt !',
                    'review_subject' => 'Merci pour votre avis, @@nomecliente@@ !',
                    'review_message' => 'Bonjour @@nomecliente@@, merci d’avoir répondu au sondage de @@nomelocale@@ ! Si vous le souhaitez, vous pouvez aussi partager votre expérience dans un avis en ligne. Merci et à bientôt !',
                ],
                'questions' => [
                    'Recommanderiez-vous @@nomelocale@@ à vos proches ?',
                    'Comment évaluez-vous la qualité des plats que vous avez goûtés ?',
                    'La présentation des plats a-t-elle répondu à vos attentes ?',
                    'Comment évaluez-vous la courtoisie et l’attention de l’équipe en salle ?',
                    'Comment avez-vous découvert @@nomelocale@@ ?',
                ],
                'comment' => 'Que pouvons-nous améliorer ?',
                'choices' => ['En passant devant', 'Amis ou famille', 'Autre'],
            ],
            'es' => [
                'email' => [
                    'title' => 'Tu experiencia en @@nomelocale@@',
                    'description' => 'Gracias por tu tiempo. Tus respuestas nos ayudan a mejorar tu experiencia.',
                    'subject' => '¡Gracias por tu visita, @@nomecliente@@!',
                    'message' => 'Hola, @@nomecliente@@. ¡Gracias por visitar @@nomelocale@@! ¿Nos cuentas cómo fue? Solo te llevará un minuto y tu opinión nos ayudará a mejorar el servicio. ¡Hasta pronto!',
                    'review_subject' => '¡Gracias por tu opinión, @@nomecliente@@!',
                    'review_message' => 'Hola, @@nomecliente@@. ¡Gracias por responder a la encuesta de @@nomelocale@@! Si quieres compartir tu experiencia, puedes hacerlo también en una reseña en línea. ¡Gracias y hasta pronto!',
                ],
                'questions' => [
                    '¿Recomendarías @@nomelocale@@ a tus amigos o familiares?',
                    '¿Cómo valoras la calidad general de los platos que probaste?',
                    '¿La presentación de los platos cumplió tus expectativas?',
                    '¿Cómo valoras la amabilidad y la atención del personal de sala?',
                    '¿Cómo conociste @@nomelocale@@?',
                ],
                'comment' => '¿Qué podemos mejorar?',
                'choices' => ['Al pasar por delante', 'Amigos o familiares', 'Otro'],
            ],
        ];
    }
}
