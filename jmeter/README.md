# JMeter tests

Run from PowerShell while the Laravel app is available at `http://127.0.0.1:8000`:

```powershell
& "..\tools\apache-jmeter-5.6.3\bin\jmeter.bat" `
  -n -t "jmeter\classroom-management-test-plan.jmx" `
  -l "jmeter\results\results.jtl" `
  -e -o "jmeter\report"
```

The test plan covers:

- Public home page under light concurrent load.
- Authentication protection for `/dashboard`.
- Valid admin login with CSRF/session handling.
- Authenticated dashboard load.
- Invalid password and expected validation message.
