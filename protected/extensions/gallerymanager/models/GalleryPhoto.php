<?php

/**
 * This is the model class for table "gallery_photo".
 *
 * The followings are the available columns in table 'gallery_photo':
 * @property integer $id
 * @property integer $gallery_id
 * @property integer $rank
 * @property string $name
 * @property string $description
 * @property string $file_name
 *
 * The followings are the available model relations:
 * @property Gallery $gallery
 *
 * @author Bogdan Savluk <savluk.bogdan@gmail.com>
 */
class GalleryPhoto extends CActiveRecord
{
    /** @var string Extensions for gallery images */
    public $galleryExt = 'jpg';
    /** @var string directory in web root for galleries */
    public $galleryDir = 'uploads/gallery';

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return GalleryPhoto the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
            return 'x2_gallery_photo';

    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('gallery_id', 'required'),
//            array('gallery_id, rank', 'numerical', 'integerOnly' => true),
            array('name', 'length', 'max' => 512),
            array('file_name', 'length', 'max' => 128),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, gallery_id, rank, name, description, file_name', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'gallery' => array(self::BELONGS_TO, 'Gallery', 'gallery_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id' => 'ID',
            'gallery_id' => 'Gallery',
            'rank' => 'Rank',
            'name' => 'Name',
            'description' => 'Description',
            'file_name' => 'File Name',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('gallery_id', $this->gallery_id);
        $criteria->compare('rank', $this->rank);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('file_name', $this->file_name, true);

        return new CActiveDataProvider($this, array(
            'criteria' => $criteria,
        ));
    }

    public function save($runValidation = true, $attributes = null)
    {

        parent::save($runValidation, $attributes);
        if ($this->rank == null) {
            $this->rank = $this->id;
            $this->setIsNewRecord(false);
            $this->save(false);
        }
        $listingId = Yii::app()->db->createCommand('SELECT modelId FROM x2_gallery_to_model WHERE modelName="Listings2" AND galleryId=:id')->bindValue('id',$this->gallery_id)->queryAll();
        if (isset($listingId[0])) {
            $listingModel = X2Model::model('Listings2')->findByPk($listingId[0]);
            $listingModel->c_website_listing_image__c = $this->getPublicUrl();
            $listingModel->save();
        }
        return true;
    }

    public function getPreview()
    {
        return Yii::app()->request->baseUrl . '/' . $this->galleryDir . '/_' . $this->getFileName('') . '.' . $this->galleryExt;
    }

    private function getFileName($version = '')
    {
        return $this->id . $version;
    }

    public function getUrl($version = '')
    {
        return Yii::app()->request->baseUrl . '/' . $this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt;
    }

    public function setImage($path)
    {
        //save image in original size
        Yii::app()->image->load($path)->save($this->galleryDir . '/' . $this->getFileName('') . '.' . $this->galleryExt);
        //create image preview for gallery manager
        Yii::app()->image->load($path)->resize(300, null)->save($this->galleryDir . '/_' . $this->getFileName('') . '.' . $this->galleryExt);

        foreach ($this->gallery->versions as $version => $actions) {
            $image = Yii::app()->image->load($path);
            foreach ($actions as $method => $args) {
                call_user_func_array(array($image, $method), is_array($args) ? $args : array($args));
            }
            $image->save($this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt);
        }
    }

    public function renderFile() {
        $filePath = $this->galleryDir . '/' . $this->getFileName('') . '.' . $this->galleryExt ;
        if ($filePath != null) {
            $file = Yii::app()->file->set($filePath);
        } else {
            throw new CHttpException(404);
        }
        if ($file->exists) {
            header('Content-type: ' . $file->mimeType);
            echo $file->getContents();
        }
    }

    public function getAccessKey() {
        if(empty($this->accessKey)){
            $accessKey = bin2hex(openssl_random_pseudo_bytes(32));
            Yii::app()->db->createCommand()->update(
                'x2_media',
                array('accessKey' => $accessKey),
                'id=:id',
                array(':id' => $this->id));
            return $accessKey;
        } else {
            return $this->accessKey;
        }
    }

    public function getPublicUrl($key = true) {
        return Yii::app()->createExternalUrl('/gallery/getFile', array(
                'id' => $this->id,
                'key' => $key?$this->getAccessKey():'',
            ));
    }


    public function delete()
    {
        $this->removeFile($this->galleryDir . '/' . $this->getFileName('') . '.' . $this->galleryExt);
        //create image preview for gallery manager
        $this->removeFile($this->galleryDir . '/_' . $this->getFileName('') . '.' . $this->galleryExt);

        foreach ($this->gallery->versions as $version => $actions) {
            $this->removeFile($this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt);
        }
        $listingId = Yii::app()->db->createCommand('SELECT modelId FROM x2_gallery_to_model WHERE modelName="Listings2" AND galleryId=:id')->bindValue('id',$this->gallery_id)->queryAll();
        if (isset($listingId[0])) {
            $listingModel = X2Model::model('Listings2')->findByPk($listingId[0]);
            if (isset(explode('/', $listingModel->c_website_listing_image__c)[7]))
                $galleryPhotoId = explode('/', $listingModel->c_website_listing_image__c)[7];
            if (isset($galleryPhotoId) && $galleryPhotoId === $this->id) {
                $nextImageId = Yii::app()->db->createCommand('SELECT id FROM x2_gallery_photo WHERE gallery_Id=:id order by rank desc limit 2')->bindValue('id',$this->gallery_id)->queryAll();
                if (isset($nextImageId[1])) {
                    $nextImage = X2Model::model('GalleryPhoto')->findByPk($nextImageId[1]);
                    $listingModel->c_website_listing_image__c = $nextImage->getPublicUrl();
                    $listingModel->save();
                } else {
                    $listingModel->c_website_listing_image__c = '';
                    $listingModel->save();
                }
            } else {
                $listingModel->c_website_listing_image__c = '';
                $listingModel->save();
            }
        }
        return parent::delete();
    }

    private function removeFile($fileName)
    {
        if (file_exists($fileName))
            @unlink($fileName);
    }

    public function removeImages()
    {
        foreach ($this->gallery->versions as $version => $actions) {
            $this->removeFile($this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt);
        }
    }

    /**
     * Regenerate image versions
     */
    public function updateImages()
    {
        foreach ($this->gallery->versions as $version => $actions) {
            $this->removeFile($this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt);

            $image = Yii::app()->image->load($this->galleryDir . '/' . $this->getFileName('') . '.' . $this->galleryExt);
            foreach ($actions as $method => $args) {
                call_user_func_array(array($image, $method), is_array($args) ? $args : array($args));
            }
            $image->save($this->galleryDir . '/' . $this->getFileName($version) . '.' . $this->galleryExt);
        }
    }


}
