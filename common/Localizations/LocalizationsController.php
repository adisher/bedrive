<?php namespace Common\Localizations;

use Common\Core\BaseController;
use Common\Database\Datasource\Datasource;
use Illuminate\Http\Request;

class LocalizationsController extends BaseController
{
    public function __construct(
        protected Request $request,
        protected LocalizationsRepository $repository,
    ) {
    }

    public function index()
    {
        $this->authorize('index', Localization::class);

        $dataSource = new Datasource(
            app(Localization::class)->newQuery(),
            request()->all(),
        );

        return $this->success(['pagination' => $dataSource->paginate()]);
    }

    public function show(string|int $idOrLangCode)
    {
        $localization = Localization::where('id', $idOrLangCode)
            ->orWhere('language', $idOrLangCode)
            ->firstOrFail();
        $this->authorize('show', $localization);

        $localization->loadLines();

        return $this->success([
            'localization' => $localization,
        ]);
    }

    public function update(int $id)
    {
        $this->authorize('update', Localization::class);

        $this->validate($this->request, [
            'name' => 'string|min:1',
            'language' => 'string|min:2|max:5',
            'lines' => 'array|min:1',
        ]);

        $localization = $this->repository->update(
            $id,
            $this->request->all(),
            true,
        );
        return $this->success(['localization' => $localization]);
    }

    public function store()
    {
        $this->authorize('store', Localization::class);

        $this->validate($this->request, [
            'name' => 'required|unique:localizations',
            'language' => 'string|min:2|max:5|unique:localizations',
        ]);

        $localization = $this->repository->create($this->request->all());
        return $this->success(['localization' => $localization]);
    }

    public function destroy(string $ids)
    {
        $localizationIds = explode(',', $ids);

        $this->authorize('destroy', Localization::class);

        foreach ($localizationIds as $id) {
            if (Localization::count() === 1) {
                return $this->error(
                    __('There must be at least one localization.'),
                );
            }
            $this->repository->delete((int) $id);
        }

        return $this->success();
    }
}
