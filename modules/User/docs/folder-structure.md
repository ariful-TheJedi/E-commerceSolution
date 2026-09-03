# Identity module folders

```text
modules/identity/
├── composer.json
├── docs/
├── src/
│   ├── IdentityServiceProvider.php  # added when the module is registered
│   ├── Contracts/
│   │   ├── Dto/
│   │   └── Events/
│   ├── Api/
│   │   ├── Routes/
│   │   ├── Controllers/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Application/
│   │   ├── RegisterAccount/
│   │   ├── Login/
│   │   ├── VerifyEmail/
│   │   ├── RecoverCredential/
│   │   ├── ChangeCredential/
│   │   ├── ManageSessions/
│   │   ├── ManageProfile/
│   │   ├── ManageAddresses/
│   │   ├── ManageAccountStatus/
│   │   ├── ManageRoles/
│   │   ├── AuthorizeRoute/
│   │   ├── ApplySecurityPolicy/
│   │   ├── ManagePrivacyRequests/
│   │   ├── RecordSecurityAudit/
│   │   ├── Ports/
│   │   └── Listeners/
│   ├── Domain/
│   │   ├── ValueObjects/
│   │   └── Rules/
│   └── Infrastructure/
│       ├── Persistence/
│       │   ├── Bootstrap/
│       │   ├── Models/
│       │   ├── Repositories/
│       │   ├── Migrations/
│       │   ├── Seeders/
│       │   └── Factories/
│       ├── Adapters/
│       └── ReadModels/
└── tests/
    ├── use-cases.txt
    ├── Unit/Domain/
    ├── Unit/Application/
    ├── Feature/Api/
    ├── Feature/Infrastructure/
    ├── Feature/Listeners/
    └── Architecture/
```
