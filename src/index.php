<?php require __DIR__ . '/../vendor/autoload.php';

session_start();

function redirect(string $location = '/', bool $permanent = false) {
    header("Location: $location", true, $permanent ? 301 : 302);
    die();
}

$client = new Google\Client();
$client->setApplicationName('Google Sheets API PHP Quickstart');
$client->setScopes('https://www.googleapis.com/auth/spreadsheets');
$client->setAuthConfig(__DIR__ . '/../credentials.json');
$client->setAccessType('offline');
$client->setPrompt('select_account consent');
$client->setRedirectUri('http://localhost');

if ($googleAuthToken = ($_SESSION['google_auth_token'] ?? '')) {
    $client->setAccessToken($googleAuthToken);
}

if ($googleAuthCode = ($_GET['code'] ?? '')) {
    $accessToken = $client->fetchAccessTokenWithAuthCode($googleAuthCode);

    if (!empty($accessToken['error'])) {
        throw new Exception(join(', ', $accessToken));
    }

    $_SESSION['google_auth_token'] = $accessToken;
    redirect();
}

if ($client->isAccessTokenExpired()) {
    if ($refreshToken = $client->getRefreshToken()) {
        $client->fetchAccessTokenWithRefreshToken($refreshToken);
        $_SESSION['google_auth_token'] = $client->getAccessToken();
        redirect();
    } else {
        $authUrl = $client->createAuthUrl();
        redirect($authUrl);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sheetName = $_POST['sheet_name'] ?? '';

    if (!$sheetName) {
        $error = 'Обязательное поле';
    } else {
        try {
            $sheet = new \Google\Service\Sheets\Sheet();
            $gridData = new \Google\Service\Sheets\GridData();

            $rows = array_map(function($n) {
                $value = new \Google\Service\Sheets\ExtendedValue();
                $value->setNumberValue($n);

                $cellData = new \Google\Service\Sheets\CellData();
                $cellData->setUserEnteredValue($value);

                $rowData = new \Google\Service\Sheets\RowData();
                $rowData->setValues([$cellData]);
                return $rowData;
            }, range(1, 10));

            $gridData->setRowData($rows);
            $sheet->setData([$gridData]);

            $props = new \Google\Service\Sheets\SpreadsheetProperties();
            $props->setTitle($sheetName);
            
            $spreadsheet = new \Google\Service\Sheets\Spreadsheet();
            $spreadsheet->setProperties($props);
            $spreadsheet->setSheets([$sheet]);

            $service = new Google\Service\Sheets($client);
            $service->spreadsheets->create($spreadsheet);
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
}

?>

<form method="post">
    <input required name="sheet_name" placeholder="Название таблицы">
    <?php if (!empty($error)) { ?><span><?php echo $error ?></span><?php } ?>
    <input type="submit" value="Создать таблицу">
</form>
