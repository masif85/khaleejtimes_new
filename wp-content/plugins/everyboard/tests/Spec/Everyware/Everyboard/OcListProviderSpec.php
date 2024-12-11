<?php /** @noinspection AccessModifierPresentedInspection */

/** @noinspection UnknownInspectionInspection */
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection PhpUnused */

namespace Spec\Everyware\Everyboard;

use Everyware\Contracts\OcObject as OcObjectInterface;
use Everyware\Everyboard\OcApiAdapter;
use Everyware\Everyboard\OcListProvider;
use Everyware\Storage\OcObjectCache;
use OcArticle;
use OcObject;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Prophecy\Prophet;

class OcListProviderSpec extends ObjectBehavior
{
    /**
     * @var OcApiAdapter
     */
    private $ocApi;

    /**
     * @var Prophet
     */
    private $prophet;

    function let(OcApiAdapter $ocApi)
    {
        $this->prophet = new Prophet();

        $this->ocApi = $ocApi;

        $this->beConstructedWith($ocApi);
    }

    function letGo()
    {
        $this->prophet->checkPredictions();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(OcListProvider::class);
    }

    function it_can_fetch_list(OcObjectInterface $object, OcObjectCache $obCache)
    {
        $uuid = 'list uuid';

        $this->ocApi->object_cache()->willReturn($obCache);

        $obCache->get($uuid)->willReturn($object);

        $this->getList($uuid)->shouldReturn($object);

    }

    function it_can_fetch_specific_properties_from_list(OcObjectInterface $object, OcObjectCache $obCache)
    {
        $uuid = 'list uuid';

        $this->ocApi->object_cache()->willReturn($obCache);

        $obCache->get($uuid)->willReturn($object);

        $object->getProperties()->willReturn($this->testListProperties());
        $this->getListProperties($uuid, ['Name', 'uuid', 'ArticleUuids'])->shouldReturn([
            "ArticleUuids" => [
                "492e06f1-4e7a-4575-a3a0-b6ee02de4e12",
                "a7228eca-5684-459b-9b3d-ef7749d23b0a",
                "8c1d44ed-e357-4e01-949a-4ddfa416bf2d",
                "73dfa1f7-672d-48ad-bfcd-b8d72f81bb3e",
                "b807fac9-cf97-4afc-913e-eb6ddf92283f",
                "41c878ea-8d09-4448-8539-ed2b402276ee",
                "494bf4cf-d166-4388-8b39-b895c095b048",
                "03618038-6fa7-4525-a3b1-f358c3ee98e5",
            ],
            "Name" => "Test List",
            "uuid" => "76d954a9-1d9c-499a-8d28-0296659ca2df",
        ]);

    }

    function it_can_fetch_a_list_of_related_object_uuids_provided_a_relational_property(OcObject $list)
    {

        $uuid = 'list uuid';
        $relation = 'Articles';
        $articleList = [];
        $uuidList = [
            "492e06f1-4e7a-4575-a3a0-b6ee02de4e12",
            "a7228eca-5684-459b-9b3d-ef7749d23b0a",
            "8c1d44ed-e357-4e01-949a-4ddfa416bf2d",
            "73dfa1f7-672d-48ad-bfcd-b8d72f81bb3e",
            "b807fac9-cf97-4afc-913e-eb6ddf92283f",
            "41c878ea-8d09-4448-8539-ed2b402276ee",
            "494bf4cf-d166-4388-8b39-b895c095b048",
            "03618038-6fa7-4525-a3b1-f358c3ee98e5",
        ];

        foreach ($uuidList as $articleUuid) {
            $article = new OcArticle;
            $article->set('uuid', [$articleUuid]);
            $articleList[] = $article;
        }

        $this->ocApi->get_single_object($uuid, ['uuid', 'contenttype', "{$relation}.uuid"], "{$relation}(start=0|limit=100)")
            ->willReturn($list);

        $list->get($relation)->willReturn($articleList);

        $this->getRelatedUuids($uuid, $relation)->shouldReturn($uuidList);
    }

    private function testListJson()
    {
        return '{"contentType":"List","editable":true,"stats":null,"properties":[{"name":"Type","type":"STRING","multiValued":false,"readOnly":false,"values":["list"]},{"name":"contenttype","type":"STRING","multiValued":false,"readOnly":true,"values":["List"]},{"name":"metadata_mimetype","type":"STRING","multiValued":true,"readOnly":true,"values":["infomaker/list-2.0"]},{"name":"deleted","type":"BOOLEAN","multiValued":false,"readOnly":true,"values":["false"]},{"name":"indexed_trigger","type":"STRING","multiValued":false,"readOnly":true,"values":["OBJECT_EVENT"]},{"name":"mimetype","type":"STRING","multiValued":false,"readOnly":true,"values":["infomaker/list-2.0"]},{"name":"primary","type":"STREAM","multiValued":false,"readOnly":true,"values":["http://oc-public-imid.tryout.infomaker.io:8443/opencontent/objects/76d954a9-1d9c-499a-8d28-0296659ca2df/files/list.xml"]},{"name":"ArticleUuids","type":"STRING","multiValued":true,"readOnly":false,"values":["492e06f1-4e7a-4575-a3a0-b6ee02de4e12","a7228eca-5684-459b-9b3d-ef7749d23b0a","8c1d44ed-e357-4e01-949a-4ddfa416bf2d","73dfa1f7-672d-48ad-bfcd-b8d72f81bb3e","b807fac9-cf97-4afc-913e-eb6ddf92283f","41c878ea-8d09-4448-8539-ed2b402276ee","494bf4cf-d166-4388-8b39-b895c095b048","03618038-6fa7-4525-a3b1-f358c3ee98e5"]},{"name":"Name","type":"STRING","multiValued":false,"readOnly":false,"values":["Lavender - Entertainment"]},{"name":"PackageUuids","type":"STRING","multiValued":true,"readOnly":false,"values":[]},{"name":"version","type":"INTEGER","multiValued":false,"readOnly":true,"values":["11"]},{"name":"eventtype","type":"STRING","multiValued":false,"readOnly":true,"values":["UPDATE"]},{"name":"source","type":"STRING","multiValued":false,"readOnly":true,"values":[]},{"name":"uuid","type":"STRING","multiValued":false,"readOnly":true,"values":["76d954a9-1d9c-499a-8d28-0296659ca2df"]},{"name":"group","type":"STRING","multiValued":true,"readOnly":true,"values":["Development","Lavender","Nigella","Zinnia"]},{"name":"updated","type":"DATE","multiValued":false,"readOnly":true,"values":["2020-11-17T10:35:06Z"]},{"name":"indexed_date","type":"DATE","multiValued":false,"readOnly":true,"values":["2020-11-17T10:35:07Z"]},{"name":"checksum","type":"STRING","multiValued":false,"readOnly":true,"values":["c7a91c63aee04871c7746a69d1402050c7a91c63aee04871c7746a69d1402050"]},{"name":"created","type":"DATE","multiValued":false,"readOnly":true,"values":["2019-10-07T14:36:19Z"]},{"name":"Products","type":"STRING","multiValued":true,"readOnly":false,"values":[]},{"name":"metadata","type":"STREAM","multiValued":true,"readOnly":true,"values":["http://oc-public-imid.tryout.infomaker.io:8443/opencontent/objects/76d954a9-1d9c-499a-8d28-0296659ca2df/files/list.xml"]}]}';
    }

    private function testListProperties()
    {
        return [
            "Type" => "list",
            "contenttype" => "List",
            "metadata_mimetype" => "infomaker/list-2.0",
            "ArticleUuids" => [
                "492e06f1-4e7a-4575-a3a0-b6ee02de4e12",
                "a7228eca-5684-459b-9b3d-ef7749d23b0a",
                "8c1d44ed-e357-4e01-949a-4ddfa416bf2d",
                "73dfa1f7-672d-48ad-bfcd-b8d72f81bb3e",
                "b807fac9-cf97-4afc-913e-eb6ddf92283f",
                "41c878ea-8d09-4448-8539-ed2b402276ee",
                "494bf4cf-d166-4388-8b39-b895c095b048",
                "03618038-6fa7-4525-a3b1-f358c3ee98e5",
            ],
            "Name" => "Test List",
            "uuid" => "76d954a9-1d9c-499a-8d28-0296659ca2df",
            "updated" => "2020-11-17T10:35:06Z",
            "checksum" => "c7a91c63aee04871c7746a69d1402050c7a91c63aee04871c7746a69d1402050",
            "created" => "2019-10-07T14:36:19Z",
            "Products" => []
        ];
    }
}
