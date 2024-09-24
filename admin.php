<?php
session_start();

// Проверка, что пользователь авторизован и имеет роль 'admin'
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Путь к файлу шаблона
$templateFile = 'template.php';

// Обработка сохранения изменений в шаблон
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Обновление значений шаблона
    $channel = $_POST['channel'];
    $callerid = $_POST['callerid'];
    $waitTime = $_POST['waitTime'];
    $maxRetries = $_POST['maxRetries'];
    $retryTime = $_POST['retryTime'];
    $application = $_POST['application'];
    $useContext = $_POST['useContext'] === 'context'; // Флаг для выбора между "Data" и "Context"

    // Обновляем файл template.php на основе введенных данных
    $templateContent = "Channel: {$channel}\n";
    $templateContent .= "Callerid: {$callerid}\n";
    $templateContent .= "WaitTime: {$waitTime}\n";
    $templateContent .= "MaxRetries: {$maxRetries}\n";
    $templateContent .= "RetryTime: {$retryTime}\n";
    $templateContent .= "Application: {$application}\n";
    $templateContent .= ($useContext ? "Context" : "Data") . ": callback-planshet\n"; // Изменение между Data и Context
    $templateContent .= "AlwaysDelete: Yes\n";  // Этот параметр всегда остается неизменным

    // Сохраняем изменения в template.php
    file_put_contents($templateFile, $templateContent);

    echo "<p>Изменения сохранены!</p>";
}

// Чтение текущих значений из template.php
$template = file_exists('template.php') ? file_get_contents('template.php') : '';

// Разбор значений из шаблона
preg_match('/Channel: (.+)/', $template, $channelMatches);
preg_match('/Callerid: (.+)/', $template, $calleridMatches);
preg_match('/WaitTime: (.+)/', $template, $waitTimeMatches);
preg_match('/MaxRetries: (.+)/', $template, $maxRetriesMatches);
preg_match('/RetryTime: (.+)/', $template, $retryTimeMatches);
preg_match('/Application: (.+)/', $template, $applicationMatches);
preg_match('/(?:Data|Context): (.+)/', $template, $dataMatches);  // Обработка как Data, так и Context

$channel = isset($channelMatches[1]) ? $channelMatches[1] : 'Local/{{number}}@indebtedness-notify/n';
$callerid = isset($calleridMatches[1]) ? $calleridMatches[1] : '781500000';
$waitTime = isset($waitTimeMatches[1]) ? $waitTimeMatches[1] : '50';
$maxRetries = isset($maxRetriesMatches[1]) ? $maxRetriesMatches[1] : '{{attempts}}';
$retryTime = isset($retryTimeMatches[1]) ? $retryTimeMatches[1] : '900';
$application = isset($applicationMatches[1]) ? $applicationMatches[1] : 'Playback';
$useContext = preg_match('/Context: /', $template) ? 'context' : 'data';  // Проверяем, используется ли Context или Data

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Панель администратора</title>
    <link rel="stylesheet" href="style/styles1.css">
</head>
<body>
    <div class="container">
        <a href="login.php" class="button-back">⏎</a>
        <header>
            <h1>Управление шаблоном</h1>
        </header>
        
        <form method="POST" action="admin.php">
            <!-- Редактирование параметров шаблона -->
            <h2>Параметры шаблона</h2>
            
            <label for="channel">Channel:</label>
            <input type="text" id="channel" name="channel" value="<?php echo htmlspecialchars($channel); ?>">

            <label for="callerid">Callerid (Укажите внешний номер):</label>
            <input type="text" id="callerid" name="callerid" value="<?php echo htmlspecialchars($callerid); ?>">

            <label for="waitTime">WaitTime (Укажите время ожидания ответа):</label>
            <input type="number" id="waitTime" name="waitTime" value="<?php echo htmlspecialchars($waitTime); ?>">

            <label for="maxRetries">MaxRetries:</label>
            <input type="text" id="maxRetries" name="maxRetries" value="<?php echo htmlspecialchars($maxRetries); ?>">

            <label for="retryTime">RetryTime (Укажите время повтора):</label>
            <input type="number" id="retryTime" name="retryTime" value="<?php echo htmlspecialchars($retryTime); ?>">

            <label for="useContext">Используемый контекст:</label>
            <select id="useContext" name="useContext">
                <option value="data" <?php if ($useContext == 'data') echo 'selected'; ?>>Data</option>
                <option value="context" <?php if ($useContext == 'context') echo 'selected'; ?>>Context</option>
            </select>

            <label for="application">Application (выбор из queue, playback, Dial):</label>
            <select id="application" name="application">
                <option value="Playback" <?php if ($application == 'Playback') echo 'selected'; ?>>Playback</option>
                <option value="queue" <?php if ($application == 'queue') echo 'selected'; ?>>Queue</option>
                <option value="Dial" <?php if ($application == 'Dial') echo 'selected'; ?>>Dial</option>
            </select>

            <input type="submit" value="Сохранить изменения">
        </form>
    </div>
</body>
</html>
