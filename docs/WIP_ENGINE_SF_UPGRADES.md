**Losse deliverables**
- Op v3.4 blijven, en deprecation meldingen wegwerken die voortkomen uit tests
- Upgrade guides doorlopen
    - In samenwerking met de Rector upgrade sets (https://github.com/rectorphp/rector-symfony)
    - https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.0.md
    - https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.1.md
    - https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.2.md
    - https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.3.md
    - https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.4.md
- Tests weer groen
- Op 4.4 alle deprecations wegwerken die voortkomen uit tests
    - In samenwerking met Rector upgrade sets
    - https://github.com/symfony/symfony/blob/4.4/UPGRADE-5.0.md
    - https://github.com/symfony/symfony/blob/4.4/UPGRADE-5.1.md
    - https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.2.md
    - https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.3.md
    - https://github.com/symfony/symfony/blob/5.4/UPGRADE-5.4.md
- Tests weer groen

Challenges:
* Verplichte upgrades gerelateerde packages
* Optionele upgrades gerelateerde packages zoveel mogelijk uitstellen? Of als losse taak oppakken
* Corto en de Corto unittests maken geen gebruik van namespaces. Dit geeft problemen bij het runnen van de tests


**Operationeel**
Is dit werkbaar? Voorzien we al issues?
Bespreken: Kunnen we een upgrade branch maken waar we steeds naartoe mergen? Zodat we kleine codereviews kunnen doen.
