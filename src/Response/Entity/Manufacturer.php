<?php

declare(strict_types=1);

namespace FINDOLOGIC\PlentyMarketsRestExporter\Response\Entity;

class Manufacturer extends Entity
{
    /** @var int */
    private $id;

    /** @var string */
    private $name;

    /** @var string */
    private $externalName;

    /** @var string */
    private $logo;

    /** @var string */
    private $url;

    /** @var string|null */
    private $street;

    /** @var string|null */
    private $houseNo;

    /** @var string|null */
    private $postcode;

    /** @var string|null */
    private $town;

    /** @var string|null */
    private $phoneNumber;

    /** @var string|null */
    private $faxNumber;

    /** @var string */
    private $email;

    /** @var int */
    private $countryId;

    /** @var int */
    private $pixmaniaBrandId;

    /** @var int */
    private $neckermannBrandId;

    /** @var int */
    private $neckermannAtEpBrandId;

    /** @var int */
    private $laRedouteBrandId;

    /** @var int */
    private $position;

    /** @var string */
    private $comment;

    /** @var string|null */
    private $updatedAt;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->name = (string)$data['name'];
        $this->externalName = (string)$data['externalName'];
        $this->logo = (string)$data['logo'];
        $this->url = (string)$data['url'];
        $this->street = is_null($data['street']) ? null : (string)$data['street'];
        $this->houseNo = is_null($data['houseNo']) ? null : (string)$data['houseNo'];
        $this->postcode = is_null($data['postcode']) ? null : (string)$data['postcode'];
        $this->town = is_null($data['town']) ? null : (string)$data['town'];
        $this->phoneNumber = is_null($data['phoneNumber']) ? null : (string)$data['phoneNumber'];
        $this->faxNumber = is_null($data['faxNumber']) ? null : (string)$data['faxNumber'];
        $this->email = (string)$data['email'];
        $this->countryId = (int)$data['countryId'];
        $this->pixmaniaBrandId = (int)$data['pixmaniaBrandId'];
        $this->neckermannBrandId = (int)$data['neckermannBrandId']; // Undocumented
        $this->neckermannAtEpBrandId = (int)$data['neckermannAtEpBrandId'];
        $this->laRedouteBrandId = (int)$data['laRedouteBrandId'];
        $this->position = (int)$data['position'];
        $this->comment = (string)$data['comment'];
        $this->updatedAt = is_null($data['updatedAt']) ? null : (string)$data['updatedAt'];
    }

    public function getData(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'externalName' => $this->externalName,
            'logo' => $this->logo,
            'url' => $this->url,
            'street' => $this->street,
            'houseNo' => $this->houseNo,
            'postcode' => $this->postcode,
            'town' => $this->town,
            'phoneNumber' => $this->phoneNumber,
            'faxNumber' => $this->faxNumber,
            'email' => $this->email,
            'countryId' => $this->countryId,
            'pixmaniaBrandId' => $this->pixmaniaBrandId,
            'neckermannBrandId' => $this->neckermannBrandId,
            'neckermannAtEpBrandId' => $this->neckermannAtEpBrandId,
            'laRedouteBrandId' => $this->laRedouteBrandId,
            'position' => $this->position,
            'comment' => $this->comment,
            'updatedAt' => $this->updatedAt
        ];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExternalName(): string
    {
        return $this->externalName;
    }

    public function getLogo(): string
    {
        return $this->logo;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getHouseNo(): ?string
    {
        return $this->houseNo;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function getTown(): ?string
    {
        return $this->town;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function getFaxNumber(): ?string
    {
        return $this->faxNumber;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getCountryId(): int
    {
        return $this->countryId;
    }

    public function getPixmaniaBrandId(): int
    {
        return $this->pixmaniaBrandId;
    }

    public function getNeckermannBrandId(): int
    {
        // Undocumented
        return $this->neckermannBrandId;
    }

    public function getNeckermannAtEpBrandId(): int
    {
        return $this->neckermannAtEpBrandId;
    }

    public function getLaRedouteBrandId(): int
    {
        return $this->laRedouteBrandId;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }
}
