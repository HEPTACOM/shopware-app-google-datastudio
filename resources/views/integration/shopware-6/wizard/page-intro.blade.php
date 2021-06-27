<div class="wizard-page wizard-page--intro">
    @markdown(trans('shopware-6/wizard.pages.intro.content'))

    <a class="sw-internal-link" href="javascript:wizardGoTo('data-config')">
        {{ trans('shopware-6/wizard.navigation.route.data-config') }}
    </a>

    @include('integration.shopware-6.wizard.navigation', [
        'next' => 'setup',
    ])
</div>
