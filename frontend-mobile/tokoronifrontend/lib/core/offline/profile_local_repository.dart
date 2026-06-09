import 'entity_cache_repository.dart';

class ProfileLocalRepository {
  ProfileLocalRepository._();
  static final ProfileLocalRepository instance = ProfileLocalRepository._();

  static const _kProfile = 'profile:current';

  Future<void> cacheProfile(Map<String, dynamic> payload) =>
      EntityCacheRepository.instance.saveMap(_kProfile, payload);

  Future<Map<String, dynamic>> getProfile() =>
      EntityCacheRepository.instance.getMap(_kProfile);
}

