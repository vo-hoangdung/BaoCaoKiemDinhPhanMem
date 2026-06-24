$ErrorActionPreference = "Stop"

$projectDir = Split-Path -Parent $MyInvocation.MyCommand.Path
$reportsDir = Join-Path $projectDir "target\surefire-reports"
$output = Join-Path $projectDir "TEST-REPORT.md"

if (-not (Test-Path $reportsDir)) {
    throw "Không tìm thấy thư mục report: $reportsDir. Hãy chạy Maven test trước."
}

$xmlFiles = Get-ChildItem $reportsDir -Filter "TEST-*.xml" | Sort-Object Name
$rows = New-Object System.Collections.Generic.List[string]
$total = 0
$passed = 0
$failed = 0
$skipped = 0

foreach ($file in $xmlFiles) {
    [xml]$xml = Get-Content $file.FullName
    foreach ($case in $xml.testsuite.testcase) {
        $total++
        $status = "PASS"
        $message = ""

        if ($case.failure -or $case.error) {
            $status = "FAIL"
            $failed++
            $node = if ($case.failure) { $case.failure } else { $case.error }
            $message = ($node.message -replace "`r?`n", " ").Trim()
        } elseif ($case.skipped) {
            $status = "SKIP"
            $skipped++
            $message = "Skipped"
        } else {
            $passed++
        }

        $rows.Add("| $($case.classname) | $($case.name) | $status | $message |")
    }
}

$content = @(
    "# Test Report - Classroom Management",
    "",
    "- Ngày tạo: $(Get-Date -Format 'yyyy-MM-dd HH:mm:ss')",
    "- Tổng số test: $total",
    "- Pass: $passed",
    "- Fail: $failed",
    "- Skip: $skipped",
    "",
    "| Test class | Test case | Kết quả | Ghi chú |",
    "|---|---|---|---|"
) + $rows

$content | Set-Content -Path $output -Encoding UTF8
Write-Host "Đã tạo report: $output"
