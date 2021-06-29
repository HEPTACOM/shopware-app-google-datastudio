<footer class="sw-button-process">
    <button
        @isset ($previous)
        class="sw-button"
        onclick="wizardGoTo({{ json_encode($previous) }})"
        @else
        class="sw-button sw-button-process__content is--hidden"
        @endisset
    >
            <span class="sw-button__content">
                {{ trans('shopware-6/wizard.navigation.previous') }}
            </span>
    </button>

    <button
        @isset ($next)
        class="sw-button sw-button--primary"
        onclick="wizardGoTo({{ json_encode($next) }})"
        @else
        class="sw-button sw-button-process__content is--hidden"
        @endisset
    >
        <span class="sw-button__content">
            {{ trans('shopware-6/wizard.navigation.next') }}
        </span>
    </button>
</footer>
