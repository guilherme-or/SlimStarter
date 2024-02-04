<?php

namespace Command;

enum FileType
{
    case Action;
    case View;
    case Entity;
    case Repository;
    case NotFoundException;
    case RepositoryImplementation;
}