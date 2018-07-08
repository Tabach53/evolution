<?php namespace EvolutionCMS\Controllers\Resources;

use EvolutionCMS\Models;
use EvolutionCMS\Controllers\AbstractResources;
use EvolutionCMS\Interfaces\ManagerTheme\TabControllerInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent;

//'actions'=>array('edit'=>array(108,'edit_module'), 'duplicate'=>array(111,'new_module'), 'remove'=>array(110,'delete_module')),
class Modules extends AbstractResources implements TabControllerInterface
{
    protected $view = 'page.resources.modules';

    /**
     * @inheritdoc
     */
    public function getTabName($withIndex = true): string
    {
        return 'tabModules' . ($withIndex ? '-' . $this->getIndex() : '');
    }

    /**
     * @inheritdoc
     */
    public function canView(): bool
    {
        return evolutionCMS()->hasAnyPermissions([
            'exec_module',
            'new_module',
            'edit_module',
            'save_module',
            'delete_module'
        ]);
    }

    protected function getBaseParams()
    {
        return array_merge(
            parent::getParameters(),
            [
                'tabPageName' => $this->getTabName(false),
                'tabIndexPageName' => $this->getTabName()
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getParameters(array $params = []) : array
    {
        $params = array_merge($this->getBaseParams(), $params);

        return $this->isNoData() ? $params : array_merge([
            'categories' => $this->parameterCategories(),
            'outCategory' => $this->parameterOutCategory(),
            'action' => $this->parameterActionName()
        ], $params);
    }

    protected function parameterOutCategory() : Collection
    {
        return Models\SiteModule::where('category', '=', 0)
            ->orderBy('name', 'ASC')
            ->lockedView()
            ->get();
    }

    protected function parameterCategories() : Collection
    {
        return Models\Category::with('modules')
            ->whereHas('modules', function (Eloquent\Builder $builder) {
                return $builder->lockedView();
            })->orderBy('rank', 'ASC')
            ->get();
    }

    protected function parameterActionName() : string
    {
        switch (true) {
            case evolutionCMS()->hasPermission('edit_module'):
                $action = 'actions.edit';
                break;
            case evolutionCMS()->hasPermission('exec_module'):
                $action = 'actions.run';
                break;
            default:
                $action = '';
        }
        return $action;
    }
}
