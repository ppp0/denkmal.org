{extends file=$render->getLayoutPath('Layout/Default/default.tpl', 'Denkmal')}

{block name='header'}
  <div class="logoWrapper">
    <a class="logo" href="{linkUrl page='Denkmal_Page_Index'}">
      <span class="logo-city">{resourceFileContent path='img/logo-city.svg'}</span>
      <span class="logo-denkmal">{resourceFileContent path='img/logo-denkmal.svg'}</span>
    </a>
  </div>
{/block}
