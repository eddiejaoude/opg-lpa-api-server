<?php

namespace Infrastructure;

interface MongoHttpConnectionProviderInterface
{
    public function getBaseMongoHttpApiUri();
}
