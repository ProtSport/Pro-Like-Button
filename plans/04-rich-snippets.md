# План: Google Rich Snippets / структуровані дані (schema.org)

## Мета
Віддавати кількість лайків/дизлайків у вигляді structured data (JSON-LD), щоб пошукові системи й інші краулери розуміли рівень залученості до контенту.

## Важливе застереження (чесно, не завищувати очікування)
Google **офіційно не показує** окремий rich-result саме для "лайків" у видачі (на відміну від зірочок-рейтингу `AggregateRating`, які показуються). Тобто ефект буде швидше "структуровані дані для машинного розуміння контенту" (можуть підхопити AI-краулери, агрегатори), а не гарантований візуальний бонус у Google Search. Це варто одразу чітко сказати замовнику/користувачам плагіна в описі фічі, щоб не було завищених очікувань.

## Вибір схеми
Два варіанти, не взаємовиключні:

1. **`InteractionCounter`** (правильніше семантично для лайків):
   ```json
   {
     "@context": "https://schema.org",
     "@type": "BlogPosting",
     "interactionStatistic": [
       {
         "@type": "InteractionCounter",
         "interactionType": "https://schema.org/LikeAction",
         "userInteractionCount": 42
       },
       {
         "@type": "InteractionCounter",
         "interactionType": "https://schema.org/DislikeAction",
         "userInteractionCount": 3
       }
     ]
   }
   ```
2. **`AggregateRating`** (якщо власник сайту хоче показати "рейтинг" 1–5, вираховуючи його з співвідношення лайк/дизлайк) — менш чесно для чистого лайк/дизлайк функціоналу, але це те, що Google реально вміє показувати як зірочки. Рахується як `rating = 1 + 4 * likes / (likes + dislikes)`.

Рекомендація: за замовчуванням **вимкнено**, у налаштуваннях — вибір "Не виводити / InteractionCounter / AggregateRating", щоб власник сайту сам вирішив, що чесніше для його контенту.

## КРИТИЧНО: конфлікт з Rank Math SEO
На цьому сайті вже активний **Rank Math** (`seo-by-rank-math`), який сам генерує JSON-LD graph для кожної сторінки. Якщо наш плагін виведе ще один окремий `<script type="application/ld+json">` — це не помилка сама по собі (Google дозволяє кілька JSON-LD блоків), але краще **інтегруватися через фільтр Rank Math** (`rank_math/json_ld`), додавши наш `interactionStatistic` у вже існуючий граф, а не створювати конкуруючий/дублюючий блок. План:
- Якщо `class_exists('RankMath')` — хукатись у `rank_math/json_ld` і домішувати дані туди.
- Якщо Rank Math не активний — виводити власний `<script>` через `wp_head`.

## Реалізація
1. Нова функція `plb_output_schema_markup()` у `front-likes.php`, хук на `wp_head` (fallback-шлях) АБО на `rank_math/json_ld` (інтеграційний шлях), залежно від виявлення Rank Math.
2. Умова показу: тільки на `is_singular()` сторінках, де ввімкнено "Where to display" для цього типу контенту, і тільки якщо налаштування "Structured data" ≠ "Не виводити".
3. Дані беруться з тих самих лічильників `counter_like`/`counter_dislike` з `wp_posts` — нових колонок у БД не потрібно, лише нова настройка (`schema_output` varchar) у `wp_prolike`.
4. Нове поле в налаштуваннях: `<select name="schema_output">` (none / interaction-counter / aggregate-rating), в General-вкладці (окремий `.plb-row`).

## Файли, які будуть змінені
- `front-likes.php` — нова функція виводу schema, хук.
- `index.php` — нове поле налаштувань + збереження.
- БД — нова колонка `schema_output` (через `plb_maybe_upgrade_db`).

## Тестування
- Перевірити через [Google Rich Results Test](https://search.google.com/test/rich-results) — немає помилок валідації.
- Перевірити, що при активному Rank Math немає ДВОХ незалежних JSON-LD блоків з дублюючими даними (тільки domішування в existing graph).
- Перевірити, що вимкнення фічі (дефолт "Не виводити") не міняє нічого на фронтенді — нуль регресій для існуючих сайтів.

## Відкриті питання
- Чи варто робити інтеграцію ще й з Yoast SEO (інший популярний SEO-плагін), чи обмежитись Rank Math + fallback?
- AggregateRating формула "рейтингу з лайк/дизлайк" — чи узгоджена вона з користувачем, чи це взагалі потрібно (може, обмежитись тільки InteractionCounter)?
