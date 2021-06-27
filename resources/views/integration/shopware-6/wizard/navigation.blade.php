<footer>
    @isset ($previous)
        <button class="sw-button" onclick="wizardGoTo({{ json_encode($previous) }})">
            <span class="sw-button__content">
                {{ trans('shopware-6/wizard.navigation.previous') }}
            </span>
        </button>
    @endisset

    @isset ($next)
        <button class="sw-button sw-button--primary" onclick="wizardGoTo({{ json_encode($next) }})">
            <span class="sw-button__content">
                {{ trans('shopware-6/wizard.navigation.next') }}
            </span>
        </button>
    @endisset
</footer>
